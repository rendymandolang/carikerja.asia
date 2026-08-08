<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\Company;
use App\Models\JobPost;
use App\Services\HiringGuardrailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HiringGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_application_receives_a_measurable_first_response_deadline(): void
    {
        $application = $this->application();
        app(HiringGuardrailService::class)->initializeApplication($application);

        $this->assertNotNull($application->fresh()->response_due_at);
        $this->assertSame(72, (int) round($application->applied_at->diffInHours($application->fresh()->response_due_at)));
    }

    public function test_recruiter_cannot_jump_from_submitted_directly_to_offer(): void
    {
        $this->expectException(ValidationException::class);
        app(HiringGuardrailService::class)->assertTransition($this->application(), 'offer');
    }

    public function test_stale_job_is_closed_and_candidate_receives_a_final_outcome(): void
    {
        Notification::fake();
        $application = $this->application();
        $application->jobPost->forceFill(['confirmation_due_at' => now()->subMinute()])->save();

        $result = app(HiringGuardrailService::class)->enforce();

        $this->assertSame(1, $result['jobs_closed']);
        $this->assertSame('closed', $application->jobPost->fresh()->status);
        $this->assertSame('position_closed', $application->fresh()->resolution);
        $this->assertNotNull($application->fresh()->finalized_at);
    }

    public function test_public_can_report_an_inactive_job(): void
    {
        $job = $this->job();
        $this->post(route('jobs.report', $job), ['reason' => 'inactive', 'details' => 'Perusahaan menyatakan posisi sudah ditutup.'])
            ->assertRedirect();

        $this->assertDatabaseHas('job_reports', ['job_post_id' => $job->id, 'reason' => 'inactive', 'status' => 'new']);
        $this->assertSame(1, $job->fresh()->report_count);
    }

    private function application(): Application
    {
        $job = $this->job();
        $candidate = CandidateProfile::create(['full_name' => 'Kandidat Uji', 'email' => 'candidate@example.test', 'country' => 'Indonesia', 'currency' => 'IDR', 'availability_status' => 'open_to_offers']);

        return Application::create(['candidate_profile_id' => $candidate->id, 'job_post_id' => $job->id, 'company_id' => $job->company_id,
            'status' => 'submitted', 'source' => 'public_job', 'applied_at' => now(), 'last_status_changed_at' => now()]);
    }

    private function job(): JobPost
    {
        $company = Company::create(['company_name' => 'Perusahaan Uji', 'slug' => 'perusahaan-uji-'.uniqid(), 'status' => 'active']);

        return JobPost::create(['company_id' => $company->id, 'title' => 'Posisi Uji', 'slug' => 'posisi-uji-'.uniqid(),
            'country' => 'Indonesia', 'employment_type' => 'full_time', 'work_arrangement' => 'onsite', 'currency' => 'IDR',
            'description' => 'Deskripsi', 'status' => 'published', 'published_at' => now(), 'last_confirmed_at' => now(), 'confirmation_due_at' => now()->addDays(30)]);
    }
}
