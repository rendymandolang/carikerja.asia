<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'source_waitlist_id',
        'full_name',
        'email',
        'phone',
        'linkedin_url',
        'indeed_url',
        'portfolio_url',
        'headline',
        'current_position',
        'desired_job_title',
        'location',
        'city',
        'province',
        'country',
        'expected_salary_min',
        'expected_salary_max',
        'currency',
        'availability_status',
        'desired_employment_type',
        'desired_work_arrangement',
        'resume_path',
        'summary',
        'notes',
    ];

    protected $casts = [
        'expected_salary_min' => 'decimal:2',
        'expected_salary_max' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceWaitlist()
    {
        return $this->belongsTo(Waitlist::class, 'source_waitlist_id');
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

    public function workExperiences()
    {
        return $this->hasMany(CandidateWorkExperience::class)->orderBy('sort_order')->latest('start_date');
    }

    public function educations()
    {
        return $this->hasMany(CandidateEducation::class)->orderBy('sort_order')->latest('start_date');
    }

    public function skills()
    {
        return $this->hasMany(CandidateSkill::class)->orderBy('sort_order')->orderBy('name');
    }

    public function expectedSalaryRangeLabel(): string
    {
        if (! $this->expected_salary_min && ! $this->expected_salary_max) {
            return '-';
        }

        $min = $this->expected_salary_min ? number_format((float) $this->expected_salary_min, 0, ',', '.') : '-';
        $max = $this->expected_salary_max ? number_format((float) $this->expected_salary_max, 0, ',', '.') : '-';

        return "{$this->currency} {$min} - {$max}";
    }
}
