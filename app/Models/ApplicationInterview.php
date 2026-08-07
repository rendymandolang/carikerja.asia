<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationInterview extends Model
{
    public const TYPES = [
        'video',
        'onsite',
        'phone',
        'other',
    ];

    public const STATUSES = [
        'scheduled',
        'completed',
        'cancelled',
    ];

    public const TIMEZONES = [
        'Asia/Jakarta',
        'Asia/Makassar',
        'Asia/Jayapura',
        'Asia/Singapore',
    ];

    protected $fillable = [
        'application_id',
        'candidate_profile_id',
        'job_post_id',
        'company_id',
        'scheduled_by_user_id',
        'google_workspace_id',
        'title',
        'interview_type',
        'scheduled_at',
        'duration_minutes',
        'timezone',
        'meeting_url',
        'google_calendar_event_id',
        'google_calendar_event_url',
        'google_meet_link',
        'google_sync_status',
        'google_sync_error',
        'google_synced_at',
        'location',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'google_synced_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

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

    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by_user_id');
    }

    public function googleWorkspace()
    {
        return $this->belongsTo(RecruiterGoogleWorkspace::class, 'google_workspace_id');
    }

    public function typeLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->interview_type));
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function scheduledAtLabel(): string
    {
        return $this->scheduled_at
            ? $this->scheduled_at->copy()->setTimezone($this->timezone)->format('d M Y H:i') . ' ' . $this->timezone
            : '-';
    }

    public function durationLabel(): string
    {
        return $this->duration_minutes . ' menit';
    }

    public function meetingUrl(): ?string
    {
        return $this->google_meet_link ?: $this->meeting_url;
    }

    public function googleSyncLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->google_sync_status ?: 'manual'));
    }
}
