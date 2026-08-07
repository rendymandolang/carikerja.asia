<?php

namespace App\Services;

use App\Models\BackupRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class BackupService
{
    public function run(string $type = 'full', ?User $triggeredBy = null): BackupRun
    {
        if (! in_array($type, BackupRun::TYPES, true)) {
            throw new RuntimeException('Invalid backup type.');
        }

        $run = BackupRun::create([
            'type' => $type,
            'status' => 'running',
            'disk' => 'local',
            'started_at' => now(),
            'triggered_by_user_id' => $triggeredBy?->id,
        ]);

        try {
            $timestamp = now()->format('Ymd-His');
            $runDir = $this->runDirectory($timestamp);
            $files = [];

            if (in_array($type, ['database', 'full'], true)) {
                $files['database'] = $this->dumpDatabase($runDir, $timestamp);
            }

            if (in_array($type, ['storage', 'full'], true)) {
                $files['storage'] = $this->archiveStorage($runDir, $timestamp);
            }

            $manifestPath = $runDir . DIRECTORY_SEPARATOR . 'manifest.json';
            File::put($manifestPath, json_encode([
                'type' => $type,
                'created_at' => now()->toIso8601String(),
                'app_url' => config('app.url'),
                'files' => collect($files)->map(fn (string $path) => $this->relativeToPrivate($path))->all(),
            ], JSON_PRETTY_PRINT));

            $size = array_sum(array_map(fn (string $path) => filesize($path) ?: 0, array_merge($files, [$manifestPath])));

            $run->forceFill([
                'status' => 'completed',
                'path' => $this->relativeToPrivate($runDir),
                'size_bytes' => $size,
                'finished_at' => now(),
                'metadata' => [
                    'files' => collect($files)->map(fn (string $path) => $this->relativeToPrivate($path))->all(),
                ],
            ])->save();

            return $run->refresh();
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => mb_substr($exception->getMessage(), 0, 2000),
            ])->save();

            throw $exception;
        }
    }

    public function prune(int $days): int
    {
        $days = max(1, $days);
        $runs = BackupRun::query()
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        $deleted = 0;

        foreach ($runs as $run) {
            if ($run->path) {
                $this->deleteBackupPath($run->path);
            }

            $run->delete();
            $deleted++;
        }

        return $deleted;
    }

    private function dumpDatabase(string $runDir, string $timestamp): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);
        $driver = $config['driver'] ?? $connection;
        $path = $runDir . DIRECTORY_SEPARATOR . "database-{$timestamp}.sql";

        if ($driver === 'sqlite') {
            $database = $this->absoluteSqlitePath($config['database'] ?? null);

            if (! $database || ! is_file($database)) {
                throw new RuntimeException('SQLite database file was not found.');
            }

            File::copy($database, $runDir . DIRECTORY_SEPARATOR . "database-{$timestamp}.sqlite");

            return $runDir . DIRECTORY_SEPARATOR . "database-{$timestamp}.sqlite";
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("Database driver [{$driver}] is not supported by the backup service yet.");
        }

        DB::connection($connection)->getPdo();

        $command = [
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
        ];

        if (! empty($config['host'])) {
            $command[] = '--host=' . $config['host'];
        }

        if (! empty($config['port'])) {
            $command[] = '--port=' . $config['port'];
        }

        if (! empty($config['unix_socket'])) {
            $command[] = '--socket=' . $config['unix_socket'];
        }

        if (! empty($config['username'])) {
            $command[] = '--user=' . $config['username'];
        }

        $command[] = $config['database'];

        $env = [];

        if (($config['password'] ?? null) !== null && $config['password'] !== '') {
            $env['MYSQL_PWD'] = $config['password'];
        }

        $process = $this->runProcess($command, base_path(), $env, 600);
        File::put($path, $process->getOutput());

        if (! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('Database dump produced an empty file.');
        }

        return $path;
    }

    private function archiveStorage(string $runDir, string $timestamp): string
    {
        $path = $runDir . DIRECTORY_SEPARATOR . "storage-{$timestamp}.zip";
        $targets = collect(['app/public', 'app/private'])
            ->filter(fn (string $target) => is_dir(storage_path($target)))
            ->values()
            ->all();

        if ($targets === []) {
            File::put($runDir . DIRECTORY_SEPARATOR . 'storage-empty.txt', 'No storage targets were found.');
            $targets = ['storage-empty.txt'];
        }

        try {
            $this->runProcess(
                array_merge(['zip', '-rq', $path], $targets, ['-x', 'app/private/' . config('operations.backup.path', 'backups') . '/*']),
                $targets === ['storage-empty.txt'] ? $runDir : storage_path(),
                [],
                600,
            );
        } catch (Throwable $exception) {
            if (! class_exists(\ZipArchive::class)) {
                throw $exception;
            }

            $this->archiveStorageWithZipArchive($path, $targets, $runDir);
        }

        if (! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('Storage archive produced an empty file.');
        }

        return $path;
    }

    private function archiveStorageWithZipArchive(string $path, array $targets, string $runDir): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create storage zip archive.');
        }

        if ($targets === ['storage-empty.txt']) {
            $zip->addFile($runDir . DIRECTORY_SEPARATOR . 'storage-empty.txt', 'storage-empty.txt');
            $zip->close();

            return;
        }

        $backupRoot = $this->normalizePath($this->backupRoot());
        $storageRoot = $this->normalizePath(storage_path());

        foreach ($targets as $target) {
            $absoluteTarget = storage_path($target);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteTarget, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
            );

            foreach ($iterator as $file) {
                $absolutePath = $file->getPathname();
                $normalizedPath = $this->normalizePath($absolutePath);

                if (str_starts_with($normalizedPath, $backupRoot)) {
                    continue;
                }

                if ($file->isFile()) {
                    $zip->addFile($absolutePath, ltrim(substr($normalizedPath, strlen($storageRoot)), '/'));
                }
            }
        }

        $zip->close();
    }

    private function runProcess(array $command, string $cwd, array $env = [], int $timeout = 300): Process
    {
        $process = new Process($command, $cwd, $env);
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new RuntimeException($message ?: 'External backup command failed.');
        }

        return $process;
    }

    private function runDirectory(string $timestamp): string
    {
        $directory = $this->backupRoot()
            . DIRECTORY_SEPARATOR . now()->format('Y')
            . DIRECTORY_SEPARATOR . now()->format('m')
            . DIRECTORY_SEPARATOR . now()->format('d')
            . DIRECTORY_SEPARATOR . $timestamp;

        File::ensureDirectoryExists($directory, 0750, true);

        return $directory;
    }

    private function backupRoot(): string
    {
        $path = storage_path('app/private/' . config('operations.backup.path', 'backups'));
        File::ensureDirectoryExists($path, 0750, true);

        return $path;
    }

    private function deleteBackupPath(string $relativePath): void
    {
        $path = storage_path('app/private/' . ltrim($relativePath, '/'));

        if (! $this->isInsideBackupRoot($path)) {
            return;
        }

        if (is_dir($path)) {
            File::deleteDirectory($path);

            return;
        }

        if (is_file($path)) {
            File::delete($path);
        }
    }

    private function isInsideBackupRoot(string $path): bool
    {
        $root = rtrim($this->normalizePath($this->backupRoot()), '/') . '/';
        $path = rtrim($this->normalizePath($path), '/') . '/';

        return str_starts_with($path, $root) && $path !== $root;
    }

    private function relativeToPrivate(string $path): string
    {
        $base = rtrim($this->normalizePath(storage_path('app/private')), '/') . '/';
        $path = $this->normalizePath($path);

        if (str_starts_with($path, $base)) {
            return ltrim(substr($path, strlen($base)), '/');
        }

        return $path;
    }

    private function absoluteSqlitePath(?string $path): ?string
    {
        if (! $path || $path === ':memory:') {
            return null;
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
