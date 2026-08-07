<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Notifications\ApplicationMessageReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ApplicationMessageController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $candidate = Auth::user()->candidateProfile;

        abort_if(! $candidate || $application->candidate_profile_id !== $candidate->id, 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $application->loadMissing(['candidateProfile.user', 'company.recruiters', 'jobPost']);

        $message = $application->messages()->create([
            'company_id' => $application->company_id,
            'candidate_profile_id' => $application->candidate_profile_id,
            'sender_user_id' => Auth::id(),
            'sender_role' => 'candidate',
            'body' => trim($validated['body']),
            'read_by_candidate_at' => now(),
        ]);

        $this->notifyRecruiters($message);

        return redirect()
            ->route('candidate.applications.show', $application)
            ->with('success', 'Pesan berhasil dikirim.');
    }

    private function notifyRecruiters(ApplicationMessage $message): void
    {
        try {
            $message->loadMissing(['application.company.recruiters', 'application.jobPost', 'application.candidateProfile', 'sender']);

            $recruiters = $message->application?->company?->recruiters()
                ->wherePivot('status', 'active')
                ->where('users.account_status', 'active')
                ->get();

            if ($recruiters?->isNotEmpty()) {
                Notification::send($recruiters, new ApplicationMessageReceivedNotification($message, 'recruiter'));
            }
        } catch (Throwable $exception) {
            Log::warning('Recruiter application message notification dispatch failed.', [
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
