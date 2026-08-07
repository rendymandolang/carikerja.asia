<?php

return [
    'scheduler_stale_after_minutes' => (int) env('OPERATIONS_SCHEDULER_STALE_MINUTES', 5),
    'backup_stale_after_hours' => (int) env('OPERATIONS_BACKUP_STALE_HOURS', 36),

    'backup' => [
        'path' => trim(env('OPERATIONS_BACKUP_PATH', 'backups'), '/'),
        'retention_days' => (int) env('OPERATIONS_BACKUP_RETENTION_DAYS', 7),
    ],
];
