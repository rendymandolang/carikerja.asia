<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use App\Notifications\ApplicationStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class RecruiterPortalController extends Controller
{
    private const JOB_STATUSES = [
        'draft',
        'published',
        'closed',
        'archived',
    ];

    public function dashboard()
    {
        $companies = $this->activeCompanies();
        $companyIds = $companies->pluck('id')->all();

        $statusCounts = Application::query()
            ->whereIn('company_id', $companyIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentApplications = Application::query()
            ->with(['candidateProfile', 'jobPost', 'company'])
            ->whereIn('company_id', $companyIds)
            ->latest('applied_at')
            ->latest()
            ->limit(8)
            ->get();

        return view('recruiter.dashboard', [
            'companies' => $companies,
            'totalJobs' => JobPost::whereIn('company_id', $companyIds)->count(),
            'openJobs' => JobPost::whereIn('company_id', $companyIds)
                ->where('status', 'published')
                ->openForApplication()
                ->count(),
            'totalApplications' => Application::whereIn('company_id', $companyIds)->count(),
            'newApplications' => (int) ($statusCounts['submitted'] ?? 0),
            'interviewApplications' => (int) ($statusCounts['interview'] ?? 0),
            'recentApplications' => $recentApplications,
        ]);
    }

    public function jobs(Request $request)
    {
        $companyIds = $this->activeCompanyIds();

        $query = JobPost::query()
            ->with('company')
            ->withCount('applications')
            ->whereIn('company_id', $companyIds)
            ->latest();

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('department', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('recruiter.jobs.index', [
            'jobs' => $query->paginate(15)->withQueryString(),
            'statuses' => self::JOB_STATUSES,
        ]);
    }

    public function createJob()
    {
        return view('recruiter.jobs.create', [
            'job' => new JobPost([
                'country' => 'Indonesia',
                'currency' => 'IDR',
                'employment_type' => 'full_time',
                'work_arrangement' => 'onsite',
                'status' => 'draft',
            ]),
            'companies' => $this->activeCompanies(),
        ]);
    }

    public function storeJob(Request $request)
    {
        $validated = $this->validateJobPost($request);

        $validated['slug'] = $this->generateUniqueSlug($validated['title']);
        $validated['created_by_user_id'] = Auth::id();

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $job = JobPost::create($validated);

        return redirect()
            ->route('recruiter.jobs.show', $job)
            ->with('success', 'Job posting berhasil dibuat.');
    }

    public function showJob(JobPost $jobPost)
    {
        $this->ensureJobAccess($jobPost);

        $applications = $jobPost->applications()
            ->with(['candidateProfile', 'company'])
            ->latest('applied_at')
            ->latest()
            ->paginate(10);

        $jobPost->load('company');

        return view('recruiter.jobs.show', [
            'job' => $jobPost,
            'applications' => $applications,
        ]);
    }

    public function editJob(JobPost $jobPost)
    {
        $this->ensureJobAccess($jobPost);

        return view('recruiter.jobs.edit', [
            'job' => $jobPost,
            'companies' => $this->activeCompanies(),
        ]);
    }

    public function updateJob(Request $request, JobPost $jobPost)
    {
        $this->ensureJobAccess($jobPost);

        $validated = $this->validateJobPost($request);

        if ($jobPost->title !== $validated['title']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $jobPost->id);
        }

        if ($validated['status'] === 'published' && ! $jobPost->published_at) {
            $validated['published_at'] = now();
        }

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = $jobPost->published_at;
        }

        $jobPost->update($validated);

        return redirect()
            ->route('recruiter.jobs.show', $jobPost)
            ->with('success', 'Job posting berhasil diperbarui.');
    }

    public function applications(Request $request)
    {
        $companyIds = $this->activeCompanyIds();
        $jobs = JobPost::query()
            ->whereIn('company_id', $companyIds)
            ->orderBy('title')
            ->get();

        $query = Application::query()
            ->with(['candidateProfile', 'jobPost', 'company'])
            ->withCount([
                'messages as unread_message_count' => fn ($messageQuery) => $messageQuery
                    ->where('sender_role', '!=', 'recruiter')
                    ->whereNull('read_by_recruiter_at'),
            ])
            ->whereIn('company_id', $companyIds)
            ->search($request->q)
            ->latest('applied_at')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('job_post_id')) {
            $query->where('job_post_id', $request->job_post_id);
        }

        return view('recruiter.applications.index', [
            'applications' => $query->paginate(15)->withQueryString(),
            'statuses' => Application::STATUSES,
            'jobs' => $jobs,
        ]);
    }

    public function showApplication(Application $application)
    {
        $this->ensureApplicationAccess($application);

        $application->messages()
            ->where('sender_role', '!=', 'recruiter')
            ->whereNull('read_by_recruiter_at')
            ->update(['read_by_recruiter_at' => now()]);

        $application->load([
            'candidateProfile.workExperiences',
            'candidateProfile.educations',
            'candidateProfile.skills',
            'jobPost.company',
            'company',
            'reviewedBy',
            'statusHistories.changedBy',
            'interviews.scheduledBy',
            'interviews.googleWorkspace',
            'messages.sender',
        ]);

        return view('recruiter.applications.show', [
            'application' => $application,
            'statuses' => Application::STATUSES,
            'googleWorkspace' => Auth::user()->googleWorkspace,
        ]);
    }

    public function updateApplication(Request $request, Application $application)
    {
        $this->ensureApplicationAccess($application);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Application::STATUSES)],
            'current_stage' => ['nullable', 'string', 'max:255'],
            'status_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $application->status;
        $newStatus = $validated['status'];
        $statusChanged = $oldStatus !== $newStatus;
        $hasStatusNote = filled($validated['status_note'] ?? null);

        $application->fill([
            'status' => $newStatus,
            'current_stage' => $validated['current_stage'] ?? null,
            'reviewed_by_user_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($statusChanged) {
            $application->last_status_changed_at = now();
        }

        $application->save();

        if ($statusChanged || $hasStatusNote) {
            $application->statusHistories()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $validated['status_note'] ?? null,
                'changed_by_user_id' => Auth::id(),
                'changed_at' => now(),
            ]);
        }

        if ($statusChanged) {
            $this->notifyCandidateStatusChange($application, $oldStatus, $validated['status_note'] ?? null);
        }

        return redirect()
            ->route('recruiter.applications.show', $application)
            ->with('success', 'Application status berhasil diperbarui.');
    }

    public function downloadResume(Application $application)
    {
        $this->ensureApplicationAccess($application);

        $resumePath = $application->resume_path ?: $application->candidateProfile?->resume_path;

        abort_unless($resumePath, 404);
        abort_unless(Storage::disk('local')->exists($resumePath), 404);

        return Storage::disk('local')->download($resumePath);
    }

    private function activeCompanies(): Collection
    {
        return Auth::user()
            ->companies()
            ->wherePivot('status', 'active')
            ->orderBy('company_name')
            ->get();
    }

    private function activeCompanyIds(): array
    {
        return $this->activeCompanies()->pluck('id')->all();
    }

    private function ensureJobAccess(JobPost $jobPost): void
    {
        abort_unless(collect($this->activeCompanyIds())->contains($jobPost->company_id), 404);
    }

    private function ensureApplicationAccess(Application $application): void
    {
        abort_unless(collect($this->activeCompanyIds())->contains($application->company_id), 404);
    }

    private function validateJobPost(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', Rule::in($this->activeCompanyIds())],
            'title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],

            'employment_type' => [
                'required',
                Rule::in([
                    'full_time',
                    'part_time',
                    'contract',
                    'internship',
                    'freelance',
                ]),
            ],

            'work_arrangement' => [
                'required',
                Rule::in([
                    'onsite',
                    'hybrid',
                    'remote',
                ]),
            ],

            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'currency' => ['required', 'string', 'max:10'],

            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],

            'application_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(self::JOB_STATUSES)],
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            JobPost::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
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
