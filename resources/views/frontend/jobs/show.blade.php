@extends('frontend.layouts.app')

@section('title', $job->title . ' - ' . ($job->company?->company_name ?: 'carikerja.asia'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($job->description), 155))
@section('canonical', route('jobs.show', $job))
@section('og_type', 'article')

@section('head')
    @php
        $employmentTypeMap = [
            'full_time' => 'FULL_TIME',
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'internship' => 'INTERN',
            'freelance' => 'CONTRACTOR',
        ];

        $jobPosting = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => strip_tags($job->description),
            'datePosted' => $job->published_at?->toDateString(),
            'employmentType' => $employmentTypeMap[$job->employment_type] ?? 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->company?->company_name ?: 'Company confidential',
                'sameAs' => $job->company?->website,
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $job->city ?: $job->location,
                    'addressRegion' => $job->province,
                    'addressCountry' => $job->country ?: 'Indonesia',
                ],
            ],
            'url' => route('jobs.show', $job),
        ];

        if ($job->application_deadline) {
            $jobPosting['validThrough'] = $job->application_deadline->endOfDay()->toAtomString();
        }

        if ($job->salary_min || $job->salary_max) {
            $jobPosting['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => $job->currency ?: 'IDR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $job->salary_min ? (float) $job->salary_min : null,
                    'maxValue' => $job->salary_max ? (float) $job->salary_max : null,
                    'unitText' => 'MONTH',
                ],
            ];
        }
    @endphp

    <script type="application/ld+json">
        {!! json_encode($jobPosting, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
<section class="hero py-5 mb-5">
    <div class="container py-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <span class="pill mb-3">Now Hiring</span>
                <h1 class="display-5 fw-bold mb-3">{{ $job->title }}</h1>
                <p class="lead text-white-50 mb-0">
                    {{ $job->company?->company_name ?: 'Company confidential' }}
                </p>
            </div>

            <div class="col-lg-4">
                <div class="bg-white text-dark rounded-4 p-4 shadow">
                    <div class="text-muted small">Application Deadline</div>
                    <div class="fw-bold fs-5">
                        {{ $job->application_deadline ? $job->application_deadline->format('d M Y') : 'Open until filled' }}
                    </div>
                    <a href="{{ route('jobs.apply.create', $job) }}" class="btn btn-primary w-100 mt-3">
                        Apply Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card content-card mb-4">
                <div class="card-body p-4">
                    <h4>Job Description</h4>
                    <div class="preline text-muted">{{ $job->description }}</div>

                    @if ($job->requirements)
                        <hr class="my-4">
                        <h4>Requirements</h4>
                        <div class="preline text-muted">{{ $job->requirements }}</div>
                    @endif

                    @if ($job->benefits)
                        <hr class="my-4">
                        <h4>Benefits</h4>
                        <div class="preline text-muted">{{ $job->benefits }}</div>
                    @endif
                </div>
            </div>

            <div class="card content-card">
                <div class="card-body p-4">
                    <h4>Apply for this Job</h4>
                    <p class="text-muted mb-3">
                        Kirim profil dan CV Anda. Lamaran akan tercatat di ATS carikerja.asia agar statusnya bisa dikelola lebih transparan.
                    </p>
                    <a href="{{ route('jobs.apply.create', $job) }}" class="btn btn-primary">
                        Apply Now
                    </a>
                    <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary">
                        Browse More Jobs
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card content-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Job Summary</h5>

                    <div class="mb-3">
                        <div class="text-muted small">Company</div>
                        <div class="fw-bold">{{ $job->company?->company_name ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Location</div>
                        <div class="fw-bold">
                            {{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: '-') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Employment Type</div>
                        <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Work Arrangement</div>
                        <div class="fw-bold">{{ ucfirst($job->work_arrangement) }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Salary</div>
                        <div class="fw-bold">{{ $job->salaryRangeLabel() }}</div>
                    </div>

                    <div>
                        <div class="text-muted small">Published</div>
                        <div class="fw-bold">{{ $job->published_at ? $job->published_at->format('d M Y') : '-' }}</div>
                    </div>
                </div>
            </div>

            @if ($job->company)
                <div class="card content-card">
                    <div class="card-body p-4">
                        <h5 class="mb-3">About Company</h5>

                        <div class="fw-bold">{{ $job->company->company_name }}</div>

                        @if ($job->company->industry)
                            <div class="text-muted">{{ $job->company->industry }}</div>
                        @endif

                        @if ($job->company->city || $job->company->province)
                            <div class="text-muted mt-2">
                                {{ trim(($job->company->city ?: '') . ', ' . ($job->company->province ?: ''), ', ') }}
                            </div>
                        @endif

                        @if ($job->company->website)
                            <a href="{{ $job->company->website }}" target="_blank" class="btn btn-outline-primary btn-sm mt-3">
                                Visit Website
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
