<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMessage extends Model
{
    protected $fillable = [
        'application_id',
        'company_id',
        'candidate_profile_id',
        'sender_user_id',
        'sender_role',
        'body',
        'read_by_candidate_at',
        'read_by_recruiter_at',
    ];

    protected $casts = [
        'read_by_candidate_at' => 'datetime',
        'read_by_recruiter_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function isFromCandidate(): bool
    {
        return $this->sender_role === 'candidate';
    }

    public function isFromRecruiter(): bool
    {
        return $this->sender_role === 'recruiter';
    }

    public function senderLabel(): string
    {
        return match ($this->sender_role) {
            'candidate' => 'Candidate',
            'recruiter' => 'Recruiter',
            'admin' => 'Admin',
            default => 'System',
        };
    }
}
