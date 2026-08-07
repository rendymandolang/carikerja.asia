<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminJobPostController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPost::query()
            ->with(['company', 'createdBy'])
            ->latest();

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_arrangement')) {
            $query->where('work_arrangement', $request->work_arrangement);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('department', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('province', 'like', "%{$keyword}%")
                    ->orWhereHas('company', function ($companyQuery) use ($keyword) {
                        $companyQuery->where('company_name', 'like', "%{$keyword}%");
                    });
            });
        }

        return view('admin.jobs.index', [
            'jobs' => $query->paginate(15)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.jobs.create', [
            'job' => new JobPost([
                'country' => 'Indonesia',
                'currency' => 'IDR',
                'employment_type' => 'full_time',
                'work_arrangement' => 'onsite',
                'status' => 'draft',
            ]),
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateJobPost($request);

        $validated['slug'] = $this->generateUniqueSlug($validated['title']);
        $validated['created_by_user_id'] = Auth::id();

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $job = JobPost::create($validated);

        return redirect()
            ->route('admin.jobs.show', $job)
            ->with('success', 'Job posting berhasil dibuat.');
    }

    public function show(JobPost $jobPost)
    {
        $jobPost->load(['company', 'createdBy']);
        $job = $jobPost;

        return view('admin.jobs.show', compact('job'));
    }

    public function edit(JobPost $jobPost)
    {
        $job = $jobPost;

        return view('admin.jobs.edit', [
            'job' => $job,
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function update(Request $request, JobPost $jobPost)
    {
        $job = $jobPost;

        $validated = $this->validateJobPost($request);

        if ($job->title !== $validated['title']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $job->id);
        }

        if ($validated['status'] === 'published' && ! $job->published_at) {
            $validated['published_at'] = now();
        }

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = $job->published_at;
        }

        $job->update($validated);

        return redirect()
            ->route('admin.jobs.show', $job)
            ->with('success', 'Job posting berhasil diperbarui.');
    }

    private function validateJobPost(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
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
            'status' => ['required', Rule::in(['draft', 'published', 'closed', 'archived'])],
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
}
