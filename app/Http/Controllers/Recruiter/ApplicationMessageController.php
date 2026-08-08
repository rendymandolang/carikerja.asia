<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Notifications\ApplicationMessageReceivedNotification;
use App\Services\HiringGuardrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplicationMessageController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->ensureApplicationAccess($application);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $application->loadMissing(['candidateProfile.user', 'company', 'jobPost']);

        $message = $application->messages()->create([
            'company_id' => $application->company_id,
            'candidate_profile_id' => $application->candidate_profile_id,
            'sender_user_id' => Auth::id(),
            'sender_role' => 'recruiter',
            'body' => trim($validated['body']),
            'read_by_recruiter_at' => now(),
        ]);

        app(HiringGuardrailService::class)->markRecruiterResponse($application);

        $this->notifyCandidate($message);

        return redirect()
            ->route('recruiter.applications.show', $application)
            ->with('success', 'Pesan berhasil dikirim.');
    }

    private function ensureApplicationAccess(Application $application): void
    {
        $companyIds = Auth::user()
            ->companies()
            ->wherePivot('status', 'active')
            ->pluck('companies.id')
            ->all();

        abort_unless(collect($companyIds)->contains($application->company_id), 404);
    }

    private function notifyCandidate(ApplicationMessage $message): void
    {
        try {
            $message->loadMissing(['application.candidateProfile.user', 'application.jobPost', 'application.company', 'sender']);

            $candidateUser = $message->application?->candidateProfile?->user;

            if ($candidateUser) {
                $candidateUser->notify(new ApplicationMessageReceivedNotification($message, 'candidate'));
            }
        } catch (Throwable $exception) {
            Log::warning('Candidate application message notification dispatch failed.', [
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
