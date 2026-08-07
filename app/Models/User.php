<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'account_status',
        'google_id',
        'avatar_url',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot([
                'company_role',
                'job_title',
                'status',
                'invited_at',
            ])
            ->withTimestamps();
    }

    public function primaryCompany()
    {
        return $this->companies()->limit(1);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRecruiter(): bool
    {
        return $this->role === 'recruiter';
    }

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function createdJobPosts()
    {
        return $this->hasMany(JobPost::class, 'created_by_user_id');
    }

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function reviewedApplications()
    {
        return $this->hasMany(Application::class, 'reviewed_by_user_id');
    }

    public function applicationStatusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'changed_by_user_id');
    }

    public function scheduledInterviews()
    {
        return $this->hasMany(ApplicationInterview::class, 'scheduled_by_user_id');
    }

    public function googleWorkspace()
    {
        return $this->hasOne(RecruiterGoogleWorkspace::class);
    }

    public function sentApplicationMessages()
    {
        return $this->hasMany(ApplicationMessage::class, 'sender_user_id');
    }

}
