<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruiterGoogleWorkspace extends Model
{
    protected $fillable = [
        'user_id',
        'google_id',
        'google_email',
        'google_name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'calendar_id',
        'status',
        'connected_at',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'scopes' => 'array',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interviews()
    {
        return $this->hasMany(ApplicationInterview::class, 'google_workspace_id');
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && filled($this->refresh_token);
    }

    public function tokenNeedsRefresh(): bool
    {
        return ! $this->token_expires_at || $this->token_expires_at->lessThan(now()->addMinute());
    }
}
