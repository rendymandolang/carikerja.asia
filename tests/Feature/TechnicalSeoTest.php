<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TechnicalSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_only_canonical_public_pages(): void
    {
        $job = $this->job();

        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('landing'), false)
            ->assertSee(route('jobs.show', $job), false)
            ->assertSee(route('companies.show', $job->company), false)
            ->assertSee(route('jobs.city', Str::slug($job->city)), false)
            ->assertSee(route('jobs.category', $job->employment_type), false)
            ->assertDontSee(route('candidate.login'), false)
            ->assertDontSee(route('recruiter.login'), false)
            ->assertDontSee('<loc>'.route('jobs.index').'</loc>', false);
    }

    public function test_private_and_filtered_pages_return_noindex_headers(): void
    {
        $this->get(route('candidate.login'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->get(route('jobs.index', ['q' => 'engineer']))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('content="noindex,nofollow,noarchive"', false);

        $this->get(route('jobs.index'))->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_google_site_verification_is_rendered_only_when_configured(): void
    {
        config(['seo.google_site_verification' => 'verification-token']);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('name="google-site-verification" content="verification-token"', false);
    }

    public function test_job_page_has_complete_job_posting_schema(): void
    {
        $job = $this->job(['work_arrangement' => 'remote']);

        $response = $this->get(route('jobs.show', $job));

        $response->assertOk()
            ->assertSee('"@type":"JobPosting"', false)
            ->assertSee('"datePosted":', false)
            ->assertSee('"validThrough":', false)
            ->assertSee('"identifier":', false)
            ->assertSee('"directApply":true', false)
            ->assertSee('"jobLocationType":"TELECOMMUTE"', false)
            ->assertSee('"applicantLocationRequirements":', false);
    }

    public function test_expired_or_inactive_job_is_gone_and_not_in_sitemap(): void
    {
        $job = $this->job(['confirmation_due_at' => now()->subMinute()]);

        $this->get(route('jobs.show', $job))->assertGone();
        $this->get(route('jobs.apply.create', $job))->assertGone();
        $this->get(route('sitemap'))->assertDontSee(route('jobs.show', $job), false);
    }

    public function test_curated_city_category_and_company_pages_are_public(): void
    {
        $job = $this->job();

        $this->get(route('jobs.city', Str::slug($job->city)))->assertOk()->assertSee($job->title);
        $this->get(route('jobs.category', $job->employment_type))->assertOk()->assertSee($job->title);
        $this->get(route('companies.show', $job->company))->assertOk()->assertSee($job->title);
    }

    private function job(array $attributes = []): JobPost
    {
        $company = Company::create([
            'company_name' => 'Perusahaan SEO',
            'slug' => 'perusahaan-seo-'.uniqid(),
            'website' => 'https://example.test',
            'status' => 'active',
        ]);

        return JobPost::create(array_merge([
            'company_id' => $company->id,
            'title' => 'Software Engineer',
            'slug' => 'software-engineer-'.uniqid(),
            'location' => 'Kantor Pusat',
            'city' => 'Makassar',
            'province' => 'Sulawesi Selatan',
            'country' => 'Indonesia',
            'employment_type' => 'full_time',
            'work_arrangement' => 'onsite',
            'currency' => 'IDR',
            'description' => 'Mengembangkan aplikasi dan menjaga kualitas sistem.',
            'requirements' => 'Memahami pengembangan aplikasi web.',
            'status' => 'published',
            'published_at' => now(),
            'last_confirmed_at' => now(),
            'confirmation_due_at' => now()->addDays(30),
        ], $attributes));
    }
}
