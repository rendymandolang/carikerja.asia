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

}
