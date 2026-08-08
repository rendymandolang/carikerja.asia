<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobReport extends Model
{
    public const REASONS = ['inactive', 'suspicious', 'misleading', 'duplicate', 'other'];

    public const STATUSES = ['new', 'reviewing', 'resolved', 'dismissed'];

    protected $fillable = ['job_post_id', 'reason', 'details', 'reporter_email', 'ip_hash', 'status', 'reviewed_at', 'reviewed_by_user_id', 'admin_notes'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
