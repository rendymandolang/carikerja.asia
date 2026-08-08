<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'source_waitlist_id',
        'company_name',
        'legal_name',
        'slug',
        'industry',
        'website',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'status',
        'notes',
        'is_verified', 'verified_at', 'verified_by_user_id', 'last_recruiter_activity_at',
        'response_rate', 'median_response_hours', 'response_sample_size',
    ];

    protected $casts = [
        'is_verified' => 'boolean', 'verified_at' => 'datetime', 'last_recruiter_activity_at' => 'datetime',
        'response_rate' => 'decimal:2', 'median_response_hours' => 'decimal:2',
    ];

    public function sourceWaitlist()
    {
        return $this->belongsTo(Waitlist::class, 'source_waitlist_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'company_role',
                'job_title',
                'status',
                'invited_at',
            ])
            ->withTimestamps();
    }

    public function recruiters()
    {
        return $this->users()->where('users.role', 'recruiter');
    }

    public function jobPosts()
    {
        return $this->hasMany(JobPost::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function interviews()
    {
        return $this->hasMany(ApplicationInterview::class);
    }

    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function isActiveResponder(): bool
    {
        return $this->response_sample_size > 0
            && (float) $this->response_rate >= (float) config('hiring.active_responder_rate', 80)
            && ($this->last_recruiter_activity_at?->gte(now()->subDays((int) config('hiring.active_responder_days', 14))) ?? false);
    }
}
