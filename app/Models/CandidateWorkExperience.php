<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateWorkExperience extends Model
{
    protected $fillable = [
        'candidate_profile_id',
        'job_title',
        'company_name',
        'employment_type',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
