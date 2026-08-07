<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailUnsubscribe extends Model
{
    protected $fillable = [
        'email',
        'token',
        'unsubscribed_at',
        'source',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function recordFor(string $email): self
    {
        return self::firstOrCreate(
            ['email' => self::normalizeEmail($email)],
            [
                'token' => Str::random(48),
                'source' => 'marketing',
            ],
        );
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }
}
