@extends('frontend.layouts.app')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)

@section('content')
<section class="hero py-5 mb-5">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="pill mb-3">Transparent Job Board</span>
                <h1 class="display-5 fw-bold mb-3">{{ $heading }}</h1>
                <p class="lead mb-0 text-white-50">{{ $intro }}</p>
            </div>

            <div class="col-lg-4">
                <div class="bg-white text-dark rounded-4 p-4 shadow">
                    <div class="fw-bold mb-1">Published Jobs</div>
                    <div class="display-6 fw-bold">{{ $jobs->total() }}</div>
                    <div class="text-muted">lowongan aktif tersedia</div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container">
    <div class="card content-card mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-control" placeholder="Job title, company, city..." value="{{ request('q') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">City</label>
                    <select name="city" class="form-select">
                        <option value="">All Cities</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" @selected(request('city') === $city)>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="employment_type" class="form-select">
                        <option value="">All</option>
                        <option value="full_time" @selected(request('employment_type') === 'full_time')>Full Time</option>
                        <option value="part_time" @selected(request('employment_type') === 'part_time')>Part Time</option>
                        <option value="contract" @selected(request('employment_type') === 'contract')>Contract</option>
                        <option value="internship" @selected(request('employment_type') === 'internship')>Internship</option>
                        <option value="freelance" @selected(request('employment_type') === 'freelance')>Freelance</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Work</label>
                    <select name="work_arrangement" class="form-select">
                        <option value="">All</option>
                        <option value="onsite" @selected(request('work_arrangement') === 'onsite')>Onsite</option>
                        <option value="hybrid" @selected(request('work_arrangement') === 'hybrid')>Hybrid</option>
                        <option value="remote" @selected(request('work_arrangement') === 'remote')>Remote</option>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($jobs as $job)
            <div class="col-lg-6">
                <div class="card job-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">
                                    <a href="{{ route('jobs.show', $job) }}" class="text-decoration-none text-dark">
                                        {{ $job->title }}
                                    </a>
                                </h4>
                                <div class="text-muted">
                                    {{ $job->company?->company_name ?: 'Company confidential' }}
                                </div>
                            </div>

                            <span class="badge bg-success align-self-start">Open</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="pill">
                                <i class="mdi mdi-map-marker-outline me-1"></i>
                                {{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: 'Location not specified') }}
                            </span>

                            <span class="pill">
                                <i class="mdi mdi-briefcase-outline me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}
                            </span>

                            <span class="pill">
                                <i class="mdi mdi-laptop me-1"></i>
                                {{ ucfirst($job->work_arrangement) }}
                            </span>
                        </div>

                        <p class="text-muted mb-3">
                            {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 170) }}
                        </p>

                        <div class="muted-box mb-3">
                            <div class="small text-muted">Salary</div>
                            <div class="fw-bold">{{ $job->salaryRangeLabel() }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Deadline:
                                {{ $job->application_deadline ? $job->application_deadline->format('d M Y') : 'Open until filled' }}
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('jobs.apply.create', $job) }}" class="btn btn-primary btn-sm">
                                    Apply
                                </a>
                                <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-primary btn-sm">
                                    View Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card content-card">
                    <div class="card-body text-center py-5">
                        <h4>Belum ada lowongan yang sesuai.</h4>
                        <p class="text-muted mb-4">
                            Coba ubah filter pencarian atau kembali lagi nanti.
                        </p>
                        <a href="{{ route('jobs.index') }}" class="btn btn-primary">Reset Filter</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $jobs->links() }}
    </div>

    <section class="mt-5" aria-labelledby="browse-links">
        <h2 id="browse-links" class="h4">Jelajahi lowongan</h2>
        <div class="d-flex flex-wrap gap-2 mt-3">
            @foreach ($cities as $city)
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('jobs.city', \Illuminate\Support\Str::slug($city)) }}">{{ $city }}</a>
            @endforeach
            @foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Kontrak', 'internship' => 'Magang', 'freelance' => 'Freelance'] as $type => $label)
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('jobs.category', $type) }}">{{ $label }}</a>
            @endforeach
        </div>
    </section>
</main>
@endsection
