<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ApplicationInterview;
use Illuminate\Support\Facades\Auth;

class CandidateInterviewController extends Controller
{
    public function index()
    {
        $candidate = Auth::user()->candidateProfile;

        $interviews = ApplicationInterview::query()
            ->with(['application.jobPost', 'application.company', 'company', 'scheduledBy'])
            ->when($candidate, fn ($query) => $query->where('candidate_profile_id', $candidate->id))
            ->when(! $candidate, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByRaw("case when status = 'scheduled' and scheduled_at >= now() then 0 else 1 end")
            ->orderBy('scheduled_at')
            ->paginate(12);

        return view('candidate.interviews.index', compact('interviews'));
    }
}
