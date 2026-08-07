<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemHeartbeat extends Model
{
    protected $fillable = [
        'key',
        'status',
        'last_ping_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'last_ping_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function isFresh(?int $minutes = null): bool
    {
        $minutes ??= config('operations.scheduler_stale_after_minutes', 5);

        return $this->last_ping_at !== null && $this->last_ping_at->greaterThanOrEqualTo(now()->subMinutes($minutes));
    }
}
