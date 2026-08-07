<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $fillable = [
        'type',
        'full_name',
        'email',
        'linkedin_url',
        'target_role',
        'contact_name',
        'company_name',
        'company_email',
        'position',
        'notes',
        'ip_address',
        'user_agent',
        'admin_status',
        'admin_notes',
        'followed_up_at',
    ];

    protected $casts = [
        'followed_up_at' => 'datetime',
    ];

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class, 'source_waitlist_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'source_waitlist_id');
    }
}
