<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Application as JobApplication;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\User;
use App\Notifications\ApplicationSubmittedNotification;
use App\Notifications\NewApplicationReceivedNotification;
use App\Services\HiringGuardrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class JobApplicationController extends Controller
{
    public function create(JobPost $jobPost)
    {
        $this->ensureJobIsOpen($jobPost);

        $jobPost->load('company');
        $job = $jobPost;

        return view('frontend.jobs.apply', compact('job'));
    }

    public function store(Request $request, JobPost $jobPost)
    {
        $this->ensureJobIsOpen($jobPost);

        $passwordRules = Auth::check() && Auth::user()->role === 'candidate'
            ? ['nullable', 'string', 'min:10', 'confirmed']
            : ['required', 'string', 'min:10', 'confirmed'];

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'portfolio_url' => ['nullable', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'current_position' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'min:0', 'gte:expected_salary_min'],
            'availability_status' => [
                'required',
                Rule::in([
                    'immediate',
                    'notice_period',
                    'open_to_offers',
                    'not_looking',
                ]),
            ],
            'summary' => ['nullable', 'string', 'max:5000'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],
            'password' => $passwordRules,
        ]);

        $result = DB::transaction(function () use ($request, $validated, $jobPost) {
            $email = strtolower(trim($validated['email']));
            $resumePath = $request->file('resume')?->store('resumes', 'local');
            $user = $this->resolveCandidateUser($email, $validated);

            $candidate = CandidateProfile::firstOrNew(['email' => $email]);

            if ($candidate->exists && $candidate->user_id && $candidate->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'email' => 'Email ini sudah terhubung dengan akun kandidat lain.',
                ]);
            }

            $candidate->fill([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'headline' => $validated['headline'] ?? null,
                'current_position' => $validated['current_position'] ?? null,
                'location' => $validated['location'] ?? null,
                'city' => $validated['city'] ?? null,
                'province' => $validated['province'] ?? null,
                'country' => 'Indonesia',
                'expected_salary_min' => $validated['expected_salary_min'] ?? null,
                'expected_salary_max' => $validated['expected_salary_max'] ?? null,
                'currency' => 'IDR',
                'availability_status' => $validated['availability_status'],
                'summary' => $validated['summary'] ?? null,
            ]);

            if ($resumePath) {
                $candidate->resume_path = $resumePath;
            }

            $candidate->save();

            $application = JobApplication::firstOrNew([
                'candidate_profile_id' => $candidate->id,
                'job_post_id' => $jobPost->id,
            ]);

            $isNewApplication = ! $application->exists;

            $application->fill([
                'company_id' => $jobPost->company_id,
                'status' => $application->status ?: 'submitted',
                'current_stage' => $application->current_stage,
                'source' => 'public_job',
                'cover_letter' => $validated['cover_letter'] ?? null,
                'resume_path' => $resumePath ?: $application->resume_path,
                'applied_at' => $application->applied_at ?: now(),
                'last_status_changed_at' => $application->last_status_changed_at ?: now(),
            ]);

            $application->save();

            if ($isNewApplication) {
                $application->statusHistories()->create([
                    'from_status' => null,
                    'to_status' => 'submitted',
                    'notes' => 'Application submitted from public job board.',
                    'changed_by_user_id' => null,
                    'changed_at' => now(),
                ]);

                return [
                    'application' => $application,
                    'is_new' => true,
                    'user' => $user,
                    'message' => 'Lamaran Anda berhasil dikirim. Anda bisa memantau statusnya dari portal kandidat.',
                ];
            }

            return [
                'application' => $application,
                'is_new' => false,
                'user' => $user,
                'message' => 'Lamaran Anda sudah tercatat sebelumnya. Data profil terbaru sudah diperbarui.',
            ];
        });

        if ($result['is_new']) {
            app(HiringGuardrailService::class)->initializeApplication($result['application']);
            $this->sendApplicationNotifications($result['application'], $result['user']);
        }

        Auth::login($result['user']);
        $request->session()->regenerate();

        $result['user']->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()
            ->route('candidate.applications.show', $result['application'])
            ->with('success', $result['message']);
    }

    private function ensureJobIsOpen(JobPost $jobPost): void
    {
        $jobPost->loadMissing('company');
        abort_if(! $jobPost->isPublished() || ! $jobPost->isOpenForApplication() || $jobPost->company?->status !== 'active', 410);
    }

    private function resolveCandidateUser(string $email, array $validated): User
    {
        $currentUser = Auth::user();

        if ($currentUser) {
            if ($currentUser->role !== 'candidate') {
                throw ValidationException::withMessages([
                    'email' => 'Silakan logout dari akun saat ini sebelum apply sebagai kandidat.',
                ]);
            }

            if ($currentUser->account_status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => 'Akun kandidat Anda belum aktif.',
                ]);
            }

            if (strtolower($currentUser->email) !== $email) {
                throw ValidationException::withMessages([
                    'email' => 'Email lamaran harus sama dengan email akun kandidat yang sedang login.',
                ]);
            }

            return $currentUser;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->role !== 'candidate') {
                throw ValidationException::withMessages([
                    'email' => 'Email ini sudah dipakai untuk akun non-kandidat.',
                ]);
            }

            if ($user->account_status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => 'Akun kandidat ini belum aktif.',
                ]);
            }

            if (! Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => 'Password tidak sesuai untuk email kandidat ini.',
                ]);
            }

            return $user;
        }

        return User::create([
            'name' => $validated['full_name'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'candidate',
            'account_status' => 'active',
        ]);
    }

    private function sendApplicationNotifications(JobApplication $application, User $candidateUser): void
    {
        try {
            $application->loadMissing(['candidateProfile', 'jobPost', 'company']);

            $candidateUser->notify(new ApplicationSubmittedNotification($application));

            $recruiters = User::query()
                ->where('role', 'recruiter')
                ->where('account_status', 'active')
                ->whereHas('companies', function ($query) use ($application) {
                    $query->where('companies.id', $application->company_id)
                        ->where('company_user.status', 'active');
                })
                ->get();

            Notification::send($recruiters, new NewApplicationReceivedNotification($application));
        } catch (Throwable $exception) {
            Log::warning('Application notification dispatch failed.', [
                'application_id' => $application->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
