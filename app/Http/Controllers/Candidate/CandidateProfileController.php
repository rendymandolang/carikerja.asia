<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidateEducation;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CandidateWorkExperience;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CandidateProfileController extends Controller
{
    public function edit()
    {
        $candidate = $this->candidateProfile()->load(['workExperiences', 'educations', 'skills']);

        return view('candidate.profile.edit', [
            'candidate' => $candidate,
            'completion' => $this->profileCompletion($candidate),
        ]);
    }

    public function update(Request $request)
    {
        $candidate = $this->candidateProfile();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'indeed_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'current_position' => ['nullable', 'string', 'max:255'],
            'desired_job_title' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'min:0', 'gte:expected_salary_min'],
            'currency' => ['required', 'string', 'max:10'],
            'availability_status' => [
                'required',
                Rule::in(['immediate', 'notice_period', 'open_to_offers', 'not_looking']),
            ],
            'desired_employment_type' => [
                'nullable',
                Rule::in(['full_time', 'part_time', 'contract', 'internship', 'freelance']),
            ],
            'desired_work_arrangement' => [
                'nullable',
                Rule::in(['onsite', 'hybrid', 'remote']),
            ],
            'summary' => ['nullable', 'string', 'max:5000'],
        ]);

        $candidate->update($validated);
        Auth::user()->update([
            'name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Profil kandidat berhasil diperbarui.');
    }

    public function updateResume(Request $request)
    {
        $validated = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
        ]);

        $candidate = $this->candidateProfile();

        if ($candidate->resume_path && Storage::disk('local')->exists($candidate->resume_path)) {
            Storage::disk('local')->delete($candidate->resume_path);
        }

        $candidate->update([
            'resume_path' => $validated['resume']->store('resumes', 'local'),
        ]);

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Resume berhasil diperbarui.');
    }

    public function downloadResume()
    {
        $candidate = $this->candidateProfile();

        abort_unless($candidate->resume_path, 404);
        abort_unless(Storage::disk('local')->exists($candidate->resume_path), 404);

        return Storage::disk('local')->download($candidate->resume_path);
    }

    public function storeExperience(Request $request)
    {
        $candidate = $this->candidateProfile();

        $candidate->workExperiences()->create($this->validateExperience($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Experience berhasil ditambahkan.');
    }

    public function updateExperience(Request $request, CandidateWorkExperience $experience)
    {
        $this->ensureOwnsSection($experience->candidate_profile_id);
        $experience->update($this->validateExperience($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Experience berhasil diperbarui.');
    }

    public function deleteExperience(CandidateWorkExperience $experience)
    {
        $this->ensureOwnsSection($experience->candidate_profile_id);
        $experience->delete();

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Experience berhasil dihapus.');
    }

    public function storeEducation(Request $request)
    {
        $candidate = $this->candidateProfile();

        $candidate->educations()->create($this->validateEducation($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Education berhasil ditambahkan.');
    }

    public function updateEducation(Request $request, CandidateEducation $education)
    {
        $this->ensureOwnsSection($education->candidate_profile_id);
        $education->update($this->validateEducation($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Education berhasil diperbarui.');
    }

    public function deleteEducation(CandidateEducation $education)
    {
        $this->ensureOwnsSection($education->candidate_profile_id);
        $education->delete();

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Education berhasil dihapus.');
    }

    public function storeSkill(Request $request)
    {
        $candidate = $this->candidateProfile();

        $candidate->skills()->create($this->validateSkill($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Skill berhasil ditambahkan.');
    }

    public function updateSkill(Request $request, CandidateSkill $skill)
    {
        $this->ensureOwnsSection($skill->candidate_profile_id);
        $skill->update($this->validateSkill($request));

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Skill berhasil diperbarui.');
    }

    public function deleteSkill(CandidateSkill $skill)
    {
        $this->ensureOwnsSection($skill->candidate_profile_id);
        $skill->delete();

        return redirect()
            ->route('candidate.profile.edit')
            ->with('success', 'Skill berhasil dihapus.');
    }

    public function jobMatches()
    {
        $candidate = $this->candidateProfile()->load('skills');
        $terms = $this->candidateTerms($candidate);

        $jobs = JobPost::query()
            ->with('company')
            ->published()
            ->openForApplication()
            ->latest('published_at')
            ->limit(100)
            ->get()
            ->map(function (JobPost $job) use ($candidate, $terms) {
                [$score, $reasons] = $this->scoreJob($candidate, $job, $terms);
                $job->match_score = $score;
                $job->match_reasons = $reasons;

                return $job;
            })
            ->filter(fn (JobPost $job) => $job->match_score > 0)
            ->sortByDesc('match_score')
            ->values()
            ->take(20);

        return view('candidate.profile.matches', compact('candidate', 'jobs'));
    }

    private function candidateProfile(): CandidateProfile
    {
        $user = Auth::user();

        $candidate = CandidateProfile::firstOrCreate(
            ['email' => strtolower($user->email)],
            [
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone,
                'country' => 'Indonesia',
                'currency' => 'IDR',
                'availability_status' => 'open_to_offers',
            ],
        );

        if (! $candidate->user_id) {
            $candidate->update(['user_id' => $user->id]);
        }

        abort_unless($candidate->user_id === $user->id, 404);

        return $candidate;
    }

    private function validateExperience(Request $request): array
    {
        return $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_current' => false, 'sort_order' => 0];
    }

    private function validateEducation(Request $request): array
    {
        return $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:255'],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['sort_order' => 0];
    }

    private function validateSkill(Request $request): array
    {
        $candidate = $this->candidateProfile();
        $skillId = $request->route('skill')?->id;

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('candidate_skills', 'name')
                    ->where('candidate_profile_id', $candidate->id)
                    ->ignore($skillId),
            ],
            'proficiency' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'expert'])],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['sort_order' => 0];
    }

    private function ensureOwnsSection(int $candidateProfileId): void
    {
        abort_unless($this->candidateProfile()->id === $candidateProfileId, 404);
    }

    private function profileCompletion(CandidateProfile $candidate): int
    {
        $checks = [
            filled($candidate->full_name),
            filled($candidate->phone),
            filled($candidate->headline),
            filled($candidate->current_position),
            filled($candidate->desired_job_title),
            filled($candidate->location) || filled($candidate->city),
            filled($candidate->summary),
            filled($candidate->resume_path),
            $candidate->skills()->exists(),
            $candidate->workExperiences()->exists(),
            $candidate->educations()->exists(),
            filled($candidate->linkedin_url) || filled($candidate->indeed_url) || filled($candidate->portfolio_url),
        ];

        return (int) round((collect($checks)->filter()->count() / count($checks)) * 100);
    }

    private function candidateTerms(CandidateProfile $candidate): Collection
    {
        return collect([
            $candidate->headline,
            $candidate->current_position,
            $candidate->desired_job_title,
            $candidate->city,
            $candidate->province,
        ])
            ->merge($candidate->skills->pluck('name'))
            ->filter()
            ->flatMap(fn ($value) => preg_split('/[\s,;|]+/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($term) => trim($term))
            ->filter(fn ($term) => strlen($term) >= 3)
            ->unique()
            ->values();
    }

    private function scoreJob(CandidateProfile $candidate, JobPost $job, Collection $terms): array
    {
        $score = 0;
        $reasons = [];
        $jobText = strtolower(implode(' ', [
            $job->title,
            $job->department,
            $job->description,
            $job->requirements,
            $job->city,
            $job->province,
        ]));

        foreach ($terms as $term) {
            if (str_contains($jobText, $term)) {
                $score += 8;
                $reasons[] = "Keyword: {$term}";
            }
        }

        if ($candidate->desired_work_arrangement && $candidate->desired_work_arrangement === $job->work_arrangement) {
            $score += 15;
            $reasons[] = 'Work arrangement cocok';
        }

        if ($candidate->desired_employment_type && $candidate->desired_employment_type === $job->employment_type) {
            $score += 15;
            $reasons[] = 'Employment type cocok';
        }

        if ($candidate->city && $job->city && strtolower($candidate->city) === strtolower($job->city)) {
            $score += 12;
            $reasons[] = 'Kota cocok';
        }

        return [min($score, 100), array_slice(array_unique($reasons), 0, 5)];
    }
}
