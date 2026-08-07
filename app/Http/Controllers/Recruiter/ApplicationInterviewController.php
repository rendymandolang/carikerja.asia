<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\RecruiterGoogleWorkspace;
use App\Notifications\InterviewScheduledNotification;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApplicationInterviewController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->ensureApplicationAccess($application);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'interview_type' => ['required', Rule::in(ApplicationInterview::TYPES)],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'timezone' => ['required', Rule::in(ApplicationInterview::TIMEZONES)],
            'meeting_url' => ['nullable', 'url', 'max:2048'],
            'create_google_meet' => ['nullable', 'boolean'],
            'location' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $shouldCreateGoogleMeet = $request->boolean('create_google_meet');
        $googleWorkspace = $shouldCreateGoogleMeet ? $this->connectedGoogleWorkspace() : null;

        if ($shouldCreateGoogleMeet && ! $googleWorkspace) {
            throw ValidationException::withMessages([
                'create_google_meet' => 'Hubungkan Google Workspace terlebih dahulu untuk membuat Google Meet otomatis.',
            ]);
        }

        $scheduledAt = Carbon::parse($validated['scheduled_at'], $validated['timezone'])->utc();

        if ($scheduledAt->lessThan(now()->subMinutes(5))) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Jadwal interview tidak boleh berada di masa lalu.',
            ]);
        }

        $application->loadMissing(['candidateProfile.user', 'jobPost', 'company.recruiters']);

        $interview = DB::transaction(function () use ($application, $validated, $scheduledAt, $googleWorkspace, $shouldCreateGoogleMeet) {
            $oldStatus = $application->status;
            $title = $validated['title'] ?: 'Interview - ' . ($application->jobPost?->title ?: 'Application');

            $interview = $application->interviews()->create([
                'candidate_profile_id' => $application->candidate_profile_id,
                'job_post_id' => $application->job_post_id,
                'company_id' => $application->company_id,
                'scheduled_by_user_id' => Auth::id(),
                'google_workspace_id' => $googleWorkspace?->id,
                'title' => $title,
                'interview_type' => $validated['interview_type'],
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $validated['duration_minutes'],
                'timezone' => $validated['timezone'],
                'meeting_url' => $validated['meeting_url'] ?? null,
                'location' => $validated['location'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'google_sync_status' => $shouldCreateGoogleMeet ? 'pending' : 'manual',
                'status' => 'scheduled',
            ]);

            $application->fill([
                'status' => 'interview',
                'current_stage' => $title,
                'reviewed_by_user_id' => Auth::id(),
                'reviewed_at' => now(),
                'last_status_changed_at' => $oldStatus === 'interview'
                    ? $application->last_status_changed_at
                    : now(),
            ]);

            $application->save();

            $application->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => 'interview',
                'notes' => 'Interview dijadwalkan: ' . $interview->scheduledAtLabel(),
                'changed_by_user_id' => Auth::id(),
                'changed_at' => now(),
            ]);

            return $interview;
        });

        if ($shouldCreateGoogleMeet && $googleWorkspace) {
            $this->syncGoogleCalendar($interview, $googleWorkspace);
        }

        $this->notifyInterviewScheduled($interview);

        $redirect = redirect()
            ->route('recruiter.applications.show', $application)
            ->with('success', 'Interview berhasil dijadwalkan.');

        if ($interview->google_sync_status === 'failed') {
            $redirect->with('warning', 'Interview tersimpan, tetapi Google Calendar/Meet gagal dibuat. Anda bisa pakai meeting link manual sementara.');
        }

        return $redirect;
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

    private function connectedGoogleWorkspace(): ?RecruiterGoogleWorkspace
    {
        $workspace = Auth::user()->googleWorkspace;

        return $workspace?->isConnected() ? $workspace : null;
    }

    private function syncGoogleCalendar(ApplicationInterview $interview, RecruiterGoogleWorkspace $workspace): void
    {
        try {
            $event = app(GoogleCalendarService::class)->createInterviewEvent($interview, $workspace);

            $interview->forceFill([
                'google_calendar_event_id' => $event['event_id'] ?? null,
                'google_calendar_event_url' => $event['event_url'] ?? null,
                'google_meet_link' => $event['meet_link'] ?? null,
                'meeting_url' => $event['meet_link'] ?? $interview->meeting_url,
                'google_sync_status' => 'synced',
                'google_sync_error' => null,
                'google_synced_at' => now(),
            ])->save();

            $workspace->forceFill([
                'last_synced_at' => now(),
                'last_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $interview->forceFill([
                'google_sync_status' => 'failed',
                'google_sync_error' => $exception->getMessage(),
            ])->save();

            $workspace->forceFill([
                'last_error' => $exception->getMessage(),
            ])->save();

            Log::warning('Google Calendar interview sync failed.', [
                'interview_id' => $interview->id,
                'google_workspace_id' => $workspace->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyInterviewScheduled(ApplicationInterview $interview): void
    {
        try {
            $interview->loadMissing([
                'application.candidateProfile.user',
                'application.jobPost',
                'company.recruiters',
            ]);

            $candidateUser = $interview->application?->candidateProfile?->user;

            if ($candidateUser) {
                $candidateUser->notify(new InterviewScheduledNotification($interview, 'candidate'));
            }

            $recruiters = $interview->company?->recruiters()
                ->wherePivot('status', 'active')
                ->where('users.account_status', 'active')
                ->get();

            if ($recruiters?->isNotEmpty()) {
                Notification::send($recruiters, new InterviewScheduledNotification($interview, 'recruiter'));
            }
        } catch (Throwable $exception) {
            Log::warning('Interview scheduled notification dispatch failed.', [
                'interview_id' => $interview->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
