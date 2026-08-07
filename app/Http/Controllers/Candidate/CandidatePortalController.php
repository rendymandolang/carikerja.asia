<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationInterview;
use Illuminate\Support\Facades\Auth;

class CandidatePortalController extends Controller
{
    public function dashboard()
    {
        $candidate = Auth::user()->candidateProfile;

        $applications = $candidate
            ? $candidate->applications()
                ->with(['jobPost.company', 'company'])
                ->latest('applied_at')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return view('candidate.dashboard', [
            'candidate' => $candidate,
            'applications' => $applications,
            'upcomingInterviews' => $candidate
                ? ApplicationInterview::query()
                    ->with(['application.jobPost', 'company'])
                    ->where('candidate_profile_id', $candidate->id)
                    ->where('status', 'scheduled')
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->limit(3)
                    ->get()
                : collect(),
            'totalApplications' => $candidate?->applications()->count() ?? 0,
            'activeApplications' => $candidate?->applications()
                ->whereNotIn('status', ['hired', 'rejected', 'withdrawn'])
                ->count() ?? 0,
            'interviewApplications' => $candidate?->applications()
                ->where('status', 'interview')
                ->count() ?? 0,
        ]);
    }

    public function applications()
    {
        $candidate = Auth::user()->candidateProfile;

        $applications = Application::query()
            ->with(['jobPost.company', 'company'])
            ->withCount([
                'messages as unread_message_count' => fn ($query) => $query
                    ->where('sender_role', '!=', 'candidate')
                    ->whereNull('read_by_candidate_at'),
            ])
            ->when($candidate, fn ($query) => $query->where('candidate_profile_id', $candidate->id))
            ->when(! $candidate, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('applied_at')
            ->latest()
            ->paginate(10);

        return view('candidate.applications.index', compact('applications'));
    }

    public function show(Application $application)
    {
        $candidate = Auth::user()->candidateProfile;

        abort_if(! $candidate || $application->candidate_profile_id !== $candidate->id, 404);

        $application->messages()
            ->where('sender_role', '!=', 'candidate')
            ->whereNull('read_by_candidate_at')
            ->update(['read_by_candidate_at' => now()]);

        $application->load([
            'candidateProfile',
            'jobPost.company',
            'company',
            'statusHistories.changedBy',
            'interviews.scheduledBy',
            'messages.sender',
        ]);

        return view('candidate.applications.show', compact('application'));
    }
}
