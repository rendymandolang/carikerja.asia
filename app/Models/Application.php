<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'submitted',
        'screening',
        'shortlisted',
        'interview',
        'offer',
        'hired',
        'rejected',
        'withdrawn',
    ];

    protected $fillable = [
        'candidate_profile_id',
        'job_post_id',
        'company_id',
        'source_waitlist_id',
        'status',
        'current_stage',
        'source',
        'cover_letter',
        'resume_path',
        'answers',
        'admin_notes',
        'applied_at',
        'last_status_changed_at',
        'reviewed_at',
        'reviewed_by_user_id',
    ];

    protected $casts = [
        'answers' => 'array',
        'applied_at' => 'datetime',
        'last_status_changed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceWaitlist()
    {
        return $this->belongsTo(Waitlist::class, 'source_waitlist_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class)->latest('changed_at');
    }

    public function interviews()
    {
        return $this->hasMany(ApplicationInterview::class)->latest('scheduled_at');
    }

    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class)->oldest();
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('candidateProfile', function ($candidateQuery) use ($keyword) {
                $candidateQuery->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            })
                ->orWhereHas('jobPost', function ($jobQuery) use ($keyword) {
                    $jobQuery->where('title', 'like', "%{$keyword}%");
                })
                ->orWhereHas('company', function ($companyQuery) use ($keyword) {
                    $companyQuery->where('company_name', 'like', "%{$keyword}%");
                });
        });
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
