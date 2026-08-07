<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateSkill extends Model
{
    protected $fillable = [
        'candidate_profile_id',
        'name',
        'proficiency',
        'years_experience',
        'sort_order',
    ];

    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
