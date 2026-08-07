@extends('candidate.layouts.app')

@section('title', 'Job Matches')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Job Matches</h1>
        <div class="text-muted">Rekomendasi berdasarkan skills, lokasi, headline, dan preferensi kerja Anda.</div>
    </div>
    <a href="{{ route('candidate.profile.edit') }}" class="btn btn-outline-primary">Update Resume Center</a>
</div>

<div class="row g-3">
    @forelse ($jobs as $job)
        <div class="col-lg-6">
            <div class="card portal-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3 mb-2">
                        <div>
                            <h2 class="h5 mb-1">{{ $job->title }}</h2>
                            <div class="text-muted">{{ $job->company?->company_name ?: 'Company confidential' }}</div>
                        </div>
                        <span class="badge bg-primary align-self-start">{{ $job->match_score }}%</span>
                    </div>

                    <div class="text-muted small mb-3">
                        {{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: '-') }}
                        &middot; {{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}
                        &middot; {{ ucfirst($job->work_arrangement) }}
                    </div>

                    @if ($job->match_reasons)
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($job->match_reasons as $reason)
                                <span class="badge text-bg-light">{{ $reason }}</span>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-primary btn-sm">View Job</a>
                    <a href="{{ route('jobs.apply.create', $job) }}" class="btn btn-primary btn-sm">Apply</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card portal-card">
                <div class="card-body text-center text-muted py-5">
                    Belum ada match. Tambahkan skills, desired job title, kota, dan preferensi kerja di Resume Center.
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
