<?php

use App\Models\SystemHeartbeat;
use App\Services\BackupService;
use App\Services\MailServerReadinessService;
use App\Services\MarketingCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ops:heartbeat {key=scheduler}', function (string $key) {
    SystemHeartbeat::updateOrCreate(
        ['key' => $key],
        [
            'status' => 'ok',
            'last_ping_at' => now(),
            'payload' => [
                'environment' => app()->environment(),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ],
        ],
    );

    $this->info("Heartbeat [{$key}] updated.");
})->purpose('Update an operations heartbeat.');

Artisan::command('ops:backup {--type=full}', function () {
    $backup = app(BackupService::class)->run($this->option('type') ?: 'full');

    $this->info("Backup completed: storage/app/private/{$backup->path}");
})->purpose('Run a platform backup.');

Artisan::command('ops:backup-prune {--days=}', function () {
    $days = (int) ($this->option('days') ?: config('operations.backup.retention_days', 7));
    $deleted = app(BackupService::class)->prune($days);

    $this->info("Pruned {$deleted} backup run(s).");
})->purpose('Delete old platform backups.');

Artisan::command('ops:mail-diagnostics', function () {
    $snapshot = app(MailServerReadinessService::class)->snapshot();

    $this->info('Mail readiness: ' . $snapshot['summary']['overall']);

    foreach ($snapshot['checks'] as $check) {
        $this->line("[{$check['status']}] {$check['label']}: {$check['message']}");
    }
})->purpose('Check mail, SMTP, and DNS readiness.');

Artisan::command('marketing:send-due', function () {
    $queued = app(MarketingCampaignService::class)->dispatchDueScheduledCampaigns();

    $this->info("Queued {$queued} scheduled marketing campaign(s).");
})->purpose('Queue scheduled marketing campaigns that are due.');

Schedule::command('ops:heartbeat scheduler')->everyMinute()->withoutOverlapping(5);
Schedule::command('marketing:send-due')->everyMinute()->withoutOverlapping(5);
Schedule::command('queue:work --stop-when-empty --queue=default --max-jobs=50 --max-time=55')->everyMinute()->withoutOverlapping(5);
Schedule::command('ops:backup --type=full')->dailyAt('02:15')->withoutOverlapping(60);
Schedule::command('ops:backup-prune --days=' . config('operations.backup.retention_days', 7))->dailyAt('03:00')->withoutOverlapping(60);
