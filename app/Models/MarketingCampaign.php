<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    public const AUDIENCES = [
        'all_contacts' => 'All contacts',
        'candidates' => 'Active candidates',
        'recruiters' => 'Active recruiters',
        'all_waitlists' => 'All waitlist contacts',
        'waitlist_candidates' => 'Waitlist candidates',
        'waitlist_recruiters' => 'Waitlist recruiters',
    ];

    protected $fillable = [
        'email_template_id',
        'name',
        'audience',
        'subject',
        'preheader',
        'body',
        'button_label',
        'button_url',
        'status',
        'recipient_count',
        'sent_count',
        'skipped_count',
        'failed_count',
        'created_by_user_id',
        'sent_by_user_id',
        'scheduled_at',
        'queued_at',
        'started_at',
        'sent_at',
        'finished_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'sent_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function recipients()
    {
        return $this->hasMany(MarketingEmailRecipient::class);
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function audienceLabel(): string
    {
        return self::AUDIENCES[$this->audience] ?? ucfirst(str_replace('_', ' ', $this->audience));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'scheduled' => 'Scheduled',
            'queued' => 'Queued',
            'sending' => 'Sending',
            'sent' => 'Sent',
            'sent_with_errors' => 'Sent with warnings',
            'failed' => 'Failed',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'sent' => 'text-bg-success',
            'sent_with_errors', 'failed' => 'text-bg-warning',
            'scheduled', 'queued', 'sending' => 'text-bg-primary',
            default => 'text-bg-secondary',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'failed', 'scheduled'], true);
    }

    public function canQueueSend(): bool
    {
        return in_array($this->status, ['draft', 'failed', 'sent_with_errors', 'scheduled'], true);
    }

    public function canSchedule(): bool
    {
        return in_array($this->status, ['draft', 'failed', 'scheduled'], true);
    }

    public function canCancelSchedule(): bool
    {
        return $this->status === 'scheduled';
    }
}
