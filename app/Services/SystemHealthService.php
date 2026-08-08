<?php

namespace App\Services;

use App\Models\BackupRun;
use App\Models\SystemHeartbeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthService
{
    public function snapshot(): array
    {
        $checks = [
            $this->databaseCheck(),
            $this->queueCheck(),
            $this->failedQueueCheck(),
            $this->schedulerCheck(),
            $this->backupCheck(),
            $this->storageCheck(),
            $this->mailCheck(),
            $this->diskCheck(),
        ];

        return [
            'summary' => $this->summary($checks),
            'checks' => $checks,
            'settings' => $this->settings(),
            'queueMetrics' => $this->queueMetrics(),
            'mailReadiness' => app(MailServerReadinessService::class)->snapshot(),
            'googleReadiness' => app(GoogleIntegrationReadinessService::class)->snapshot(),
            'backupRuns' => Schema::hasTable('backup_runs')
                ? BackupRun::with('triggeredBy')->latest()->limit(8)->get()
                : collect(),
            'schedulerHeartbeat' => Schema::hasTable('system_heartbeats')
                ? SystemHeartbeat::where('key', 'scheduler')->first()
                : null,
        ];
    }

    private function databaseCheck(): array
    {
        try {
            DB::select('select 1');

            return $this->check('database', 'Database', 'ok', 'Database connection is healthy.');
        } catch (Throwable $exception) {
            return $this->check('database', 'Database', 'critical', $exception->getMessage());
        }
    }

    private function queueCheck(): array
    {
        $connection = config('queue.default');

        if ($connection === 'sync') {
            return $this->check('queue', 'Queue', 'warning', 'Queue is using sync mode.');
        }

        if ($connection === 'database' && ! Schema::hasTable('jobs')) {
            return $this->check('queue', 'Queue', 'critical', 'Database queue table is missing.');
        }

        $pending = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

        if ($pending > 500) {
            return $this->check('queue', 'Queue', 'warning', "{$pending} pending jobs need attention.");
        }

        return $this->check('queue', 'Queue', 'ok', "{$pending} pending jobs.");
    }

    private function failedQueueCheck(): array
    {
        if (! Schema::hasTable('failed_jobs')) {
            return $this->check('failed_jobs', 'Failed Jobs', 'warning', 'Failed job table is not available yet.');
        }

        $failed = DB::table('failed_jobs')->count();

        if ($failed > 0) {
            return $this->check('failed_jobs', 'Failed Jobs', 'warning', "{$failed} failed jobs recorded.");
        }

        return $this->check('failed_jobs', 'Failed Jobs', 'ok', 'No failed jobs.');
    }

    private function schedulerCheck(): array
    {
        if (! Schema::hasTable('system_heartbeats')) {
            return $this->check('scheduler', 'Scheduler', 'critical', 'Scheduler heartbeat table is missing.');
        }

        $heartbeat = SystemHeartbeat::where('key', 'scheduler')->first();

        if (! $heartbeat) {
            return $this->check('scheduler', 'Scheduler', 'warning', 'Scheduler heartbeat has not run yet.');
        }

        if (! $heartbeat->isFresh()) {
            return $this->check(
                'scheduler',
                'Scheduler',
                'warning',
                'Last heartbeat: ' . $heartbeat->last_ping_at?->diffForHumans(),
            );
        }

        return $this->check('scheduler', 'Scheduler', 'ok', 'Last heartbeat: ' . $heartbeat->last_ping_at->diffForHumans());
    }

    private function backupCheck(): array
    {
        if (! Schema::hasTable('backup_runs')) {
            return $this->check('backup', 'Backup', 'critical', 'Backup table is missing.');
        }

        $latestCompleted = BackupRun::where('status', 'completed')->latest('finished_at')->first();
        $latestFailed = BackupRun::where('status', 'failed')->latest('finished_at')->first();

        if (! $latestCompleted) {
            return $this->check('backup', 'Backup', $latestFailed ? 'critical' : 'warning', 'No completed backup yet.');
        }

        if ($latestFailed && $latestFailed->finished_at?->greaterThan($latestCompleted->finished_at)) {
            return $this->check('backup', 'Backup', 'warning', 'Latest backup attempt failed.');
        }

        $staleAfter = (int) config('operations.backup_stale_after_hours', 36);

        if ($latestCompleted->finished_at?->lessThan(now()->subHours($staleAfter))) {
            return $this->check('backup', 'Backup', 'warning', 'Latest backup is older than ' . $staleAfter . ' hours.');
        }

        return $this->check('backup', 'Backup', 'ok', 'Latest backup: ' . $latestCompleted->finished_at?->diffForHumans());
    }

    private function storageCheck(): array
    {
        $paths = [
            storage_path(),
            storage_path('app/private'),
            storage_path('app/public'),
            storage_path('app/private/' . config('operations.backup.path', 'backups')),
        ];

        $unwritable = collect($paths)
            ->reject(function (string $path) {
                if (is_dir($path)) {
                    return is_writable($path);
                }

                $parent = dirname($path);

                return is_dir($parent) && is_writable($parent);
            })
            ->values();

        if ($unwritable->isNotEmpty()) {
            return $this->check('storage', 'Storage', 'critical', 'Unwritable path: ' . $unwritable->first());
        }

        return $this->check('storage', 'Storage', 'ok', 'Storage paths are writable.');
    }

    private function mailCheck(): array
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return $this->check('mail', 'Mail', 'warning', "Mailer is set to {$mailer}; outbound email is not live.");
        }

        if ($mailer === 'smtp' && blank(config('mail.mailers.smtp.host'))) {
            return $this->check('mail', 'Mail', 'critical', 'SMTP host is not configured.');
        }

        return $this->check('mail', 'Mail', 'ok', "Mailer is set to {$mailer}.");
    }

    private function diskCheck(): array
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());

        if ($free === false || $total === false || $total <= 0) {
            return $this->check('disk', 'Disk', 'warning', 'Disk usage could not be read.');
        }

        $freePercent = ($free / $total) * 100;

        if ($freePercent < 10) {
            return $this->check('disk', 'Disk', 'critical', round($freePercent, 1) . '% disk space free.');
        }

        if ($freePercent < 20) {
            return $this->check('disk', 'Disk', 'warning', round($freePercent, 1) . '% disk space free.');
        }

        return $this->check('disk', 'Disk', 'ok', round($freePercent, 1) . '% disk space free.');
    }

    private function queueMetrics(): array
    {
        $oldestJob = Schema::hasTable('jobs') ? DB::table('jobs')->min('created_at') : null;

        return [
            'connection' => config('queue.default'),
            'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
            'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
            'oldest_job_at' => $oldestJob ? Carbon::createFromTimestamp($oldestJob) : null,
        ];
    }

    private function settings(): array
    {
        return [
            ['label' => 'Environment', 'value' => config('app.env')],
            ['label' => 'Debug', 'value' => config('app.debug') ? 'Enabled' : 'Disabled'],
            ['label' => 'App URL', 'value' => config('app.url')],
            ['label' => 'Timezone', 'value' => config('app.timezone')],
            ['label' => 'Locale', 'value' => config('app.locale')],
            ['label' => 'Queue', 'value' => config('queue.default')],
            ['label' => 'Mailer', 'value' => config('mail.default')],
            ['label' => 'Mail From', 'value' => config('mail.from.name') . ' <' . config('mail.from.address') . '>'],
            ['label' => 'Backup Retention', 'value' => config('operations.backup.retention_days', 7) . ' days'],
        ];
    }

    private function summary(array $checks): array
    {
        $statuses = collect($checks)->pluck('status');
        $overall = $statuses->contains('critical') ? 'critical' : ($statuses->contains('warning') ? 'warning' : 'ok');

        return [
            'overall' => $overall,
            'ok' => $statuses->filter(fn (string $status) => $status === 'ok')->count(),
            'warning' => $statuses->filter(fn (string $status) => $status === 'warning')->count(),
            'critical' => $statuses->filter(fn (string $status) => $status === 'critical')->count(),
        ];
    }

    private function check(string $key, string $label, string $status, string $message): array
    {
        return compact('key', 'label', 'status', 'message');
    }
}
