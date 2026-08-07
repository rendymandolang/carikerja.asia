<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Notifications\ApplicationStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::query()
            ->with(['candidateProfile', 'jobPost', 'company'])
            ->search($request->q)
            ->latest('applied_at')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('job_post_id')) {
            $query->where('job_post_id', $request->job_post_id);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        return view('admin.applications.index', [
            'applications' => $query->paginate(15)->withQueryString(),
            'statuses' => Application::STATUSES,
            'companies' => Company::orderBy('company_name')->get(),
            'jobs' => JobPost::orderBy('title')->get(),
        ]);
    }

    public function show(Application $application)
    {
        $application->load([
            'candidateProfile.workExperiences',
            'candidateProfile.educations',
            'candidateProfile.skills',
            'jobPost.company',
            'company',
            'reviewedBy',
            'statusHistories.changedBy',
            'interviews.scheduledBy',
            'messages.sender',
        ]);

        return view('admin.applications.show', [
            'application' => $application,
            'statuses' => Application::STATUSES,
        ]);
    }

    public function update(Request $request, Application $application)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Application::STATUSES)],
            'current_stage' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'status_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $application->status;
        $newStatus = $validated['status'];
        $statusChanged = $oldStatus !== $newStatus;

        $application->fill([
            'status' => $newStatus,
            'current_stage' => $validated['current_stage'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by_user_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($statusChanged) {
            $application->last_status_changed_at = now();
        }

        $application->save();

        if ($statusChanged) {
            $application->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $validated['status_note'] ?? null,
                'changed_by_user_id' => Auth::id(),
                'changed_at' => now(),
            ]);

            $this->notifyCandidateStatusChange($application, $oldStatus, $validated['status_note'] ?? null);
        }

        return redirect()
            ->route('admin.applications.show', $application)
            ->with('success', 'Application status berhasil diperbarui.');
    }

    public function downloadResume(Application $application)
    {
        $resumePath = $application->resume_path ?: $application->candidateProfile?->resume_path;

        abort_unless($resumePath, 404);
        abort_unless(Storage::disk('local')->exists($resumePath), 404);

        return Storage::disk('local')->download($resumePath);
    }

    private function notifyCandidateStatusChange(Application $application, ?string $oldStatus, ?string $note): void
    {
        try {
            $application->loadMissing(['candidateProfile.user', 'jobPost', 'company']);

            $candidateUser = $application->candidateProfile?->user;

            if (! $candidateUser) {
                return;
            }

            $candidateUser->notify(new ApplicationStatusUpdatedNotification($application, $oldStatus, $note));
        } catch (Throwable $exception) {
            Log::warning('Candidate status notification dispatch failed.', [
                'application_id' => $application->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
