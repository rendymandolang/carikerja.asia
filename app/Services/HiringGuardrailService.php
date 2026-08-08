<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use App\Notifications\ApplicationResolvedNotification;
use App\Notifications\RecruiterSlaReminderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

class HiringGuardrailService
{
    public function initializeApplication(Application $application): void
    {
        if (! $application->response_due_at) {
            $application->forceFill(['response_due_at' => ($application->applied_at ?: now())->copy()->addHours(config('hiring.first_response_hours', 72))])->save();
        }
    }

    public function initializePublishedJob(JobPost $job): void
    {
        if ($job->status !== 'published') {
            return;
        }
        $confirmed = $job->last_confirmed_at ?: now();
        $job->forceFill(['last_confirmed_at' => $confirmed, 'confirmation_due_at' => $confirmed->copy()->addDays(config('hiring.job_confirmation_days', 30)), 'auto_paused_at' => null])->save();
    }

    public function assertTransition(Application $application, string $newStatus, bool $adminOverride = false): void
    {
        if ($application->isFinalized() && $newStatus !== $application->status) {
            throw ValidationException::withMessages(['status' => 'Lamaran yang sudah memiliki hasil akhir tidak dapat dipindahkan lagi.']);
        }
        if (! $adminOverride && ! $application->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages(['status' => "Status {$application->statusLabel()} tidak dapat langsung dipindahkan ke ".ucfirst($newStatus).'.']);
        }
    }

    public function markRecruiterResponse(Application $application): void
    {
        $updates = [];
        if (! $application->first_responded_at) {
            $updates['first_responded_at'] = now();
        }
        if ($updates) {
            $application->forceFill($updates)->save();
        }
        if ($application->company) {
            $application->company->forceFill(['last_recruiter_activity_at' => now()])->save();
        }
        $this->refreshCompanyMetrics($application->company_id);
    }

    public function finalize(Application $application, string $resolution, string $reason): void
    {
        if ($application->isFinalized()) {
            return;
        }
        $application->forceFill(['resolution' => $resolution, 'final_reason' => trim($reason), 'finalized_at' => now()])->save();
        $application->loadMissing(['candidateProfile.user', 'jobPost', 'company']);
        try {
            $application->candidateProfile?->user?->notify(new ApplicationResolvedNotification($application));
        } catch (Throwable $exception) {
            Log::warning('Application resolution notification failed.', ['application_id' => $application->id, 'error' => $exception->getMessage()]);
        }
    }

    public function confirmJob(JobPost $job): void
    {
        $job->forceFill(['status' => 'published', 'published_at' => $job->published_at ?: now(), 'last_confirmed_at' => now(),
            'confirmation_due_at' => now()->addDays(config('hiring.job_confirmation_days', 30)), 'auto_paused_at' => null,
            'closed_at' => null, 'closure_type' => null, 'closed_reason' => null])->save();
        $job->company?->forceFill(['last_recruiter_activity_at' => now()])->save();
    }

    public function closeJob(JobPost $job, string $type, string $reason, bool $automatic = false): int
    {
        return DB::transaction(function () use ($job, $type, $reason, $automatic) {
            $job->forceFill(['status' => 'closed', 'closed_at' => now(), 'closure_type' => $type, 'closed_reason' => trim($reason),
                'auto_paused_at' => $automatic ? now() : null])->save();
            $resolution = $type === 'cancelled' ? 'position_cancelled' : 'position_closed';
            $count = 0;
            $job->applications()->whereNull('finalized_at')->get()->each(function ($application) use ($resolution, $reason, &$count) {
                $this->finalize($application, $resolution, $reason);
                $count++;
            });

            return $count;
        });
    }

    public function enforce(): array
    {
        $counts = ['pre_due' => 0, 'overdue' => 0, 'jobs_closed' => 0, 'applications_finalized' => 0];
        $before = now()->addHours(config('hiring.reminder_before_hours', 24));
        Application::with(['candidateProfile', 'jobPost', 'company'])->whereNull('first_responded_at')->whereNull('finalized_at')
            ->whereNull('pre_due_reminder_sent_at')->whereBetween('response_due_at', [now(), $before])->chunkById(100, function ($apps) use (&$counts) {
                foreach ($apps as $app) {
                    $this->notifyRecruiters($app, false);
                    $app->forceFill(['pre_due_reminder_sent_at' => now()])->save();
                    $counts['pre_due']++;
                }
            });
        Application::with(['candidateProfile', 'jobPost', 'company'])->whereNull('first_responded_at')->whereNull('finalized_at')
            ->whereNull('overdue_reminder_sent_at')->where('response_due_at', '<=', now())->chunkById(100, function ($apps) use (&$counts) {
                foreach ($apps as $app) {
                    $this->notifyRecruiters($app, true);
                    $app->forceFill(['overdue_reminder_sent_at' => now()])->save();
                    $counts['overdue']++;
                }
            });
        JobPost::with('company')->where('status', 'published')->where('confirmation_due_at', '<=', now())->chunkById(50, function ($jobs) use (&$counts) {
            foreach ($jobs as $job) {
                $counts['applications_finalized'] += $this->closeJob($job, 'inactive', 'Lowongan dihentikan otomatis karena perusahaan tidak mengonfirmasi bahwa posisi masih aktif.', true);
                $counts['jobs_closed']++;
            }
        });
        Company::pluck('id')->each(fn ($id) => $this->refreshCompanyMetrics($id));

        return $counts;
    }

    public function refreshCompanyMetrics(?int $companyId): void
    {
        if (! $companyId || ! ($company = Company::find($companyId))) {
            return;
        }
        $apps = Application::where('company_id', $companyId)->whereNotNull('response_due_at')->get(['applied_at', 'created_at', 'first_responded_at']);
        $total = $apps->count();
        $responded = $apps->whereNotNull('first_responded_at');
        $hours = $responded->map(fn ($app) => ($app->applied_at ?: $app->created_at)->diffInMinutes($app->first_responded_at) / 60)->sort()->values();
        $median = null;
        if ($hours->isNotEmpty()) {
            $middle = intdiv($hours->count(), 2);
            $median = $hours->count() % 2 ? $hours[$middle] : ($hours[$middle - 1] + $hours[$middle]) / 2;
        }
        $company->forceFill(['response_sample_size' => $total, 'response_rate' => $total ? round($responded->count() / $total * 100, 2) : null,
            'median_response_hours' => $median === null ? null : round($median, 2)])->save();
    }

    private function notifyRecruiters(Application $application, bool $overdue): void
    {
        $recruiters = User::where('role', 'recruiter')->where('account_status', 'active')->whereHas('companies', fn ($q) => $q->where('companies.id', $application->company_id)->where('company_user.status', 'active'))->get();
        try {
            Notification::send($recruiters, new RecruiterSlaReminderNotification($application, $overdue));
        } catch (Throwable $exception) {
            Log::warning('Recruiter SLA reminder failed.', ['application_id' => $application->id, 'overdue' => $overdue, 'error' => $exception->getMessage()]);
        }
    }
}
