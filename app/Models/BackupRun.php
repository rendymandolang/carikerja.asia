<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRun extends Model
{
    public const TYPES = ['full', 'database', 'storage'];

    public const STATUSES = ['running', 'completed', 'failed'];

    protected $fillable = [
        'type',
        'status',
        'disk',
        'path',
        'size_bytes',
        'started_at',
        'finished_at',
        'error_message',
        'triggered_by_user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
}

    public function sizeLabel(): string
    {
        $bytes = (float) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        foreach ($units as $unit) {
            if ($bytes < 1024 || $unit === 'GB') {
                return round($bytes, $unit === 'B' ? 0 : 2) . ' ' . $unit;
            }

            $bytes /= 1024;
        }

        return '0 B';
    }
}
