@extends('candidate.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard Kandidat</h1>
        <div class="text-muted">{{ auth()->user()->name }} - {{ auth()->user()->email }}</div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('candidate.profile.edit') }}" class="btn btn-outline-primary">Resume Center</a>
        <a href="{{ route('candidate.job-matches.index') }}" class="btn btn-primary">View Matches</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Total Applications</div>
                <div class="display-6 fw-bold">{{ $totalApplications }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Active Process</div>
                <div class="display-6 fw-bold">{{ $activeApplications }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Interview</div>
                <div class="display-6 fw-bold">{{ $interviewApplications }}</div>
            </div>
        </div>
    </div>
</div>

@if ($upcomingInterviews->isNotEmpty())
    <div class="card portal-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Upcoming Interviews</strong>
            <a href="{{ route('candidate.interviews.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>

        <div class="list-group list-group-flush">
            @foreach ($upcomingInterviews as $interview)
                <div class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <strong>{{ $interview->title }}</strong>
                            <div>{{ $interview->application?->jobPost?->title ?: 'Application' }}</div>
                            <div class="text-muted small">{{ $interview->company?->company_name ?: '-' }}</div>
                            <div class="mt-2">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                {{ $interview->scheduledAtLabel() }}
                            </div>
                        </div>

                        <a href="{{ route('candidate.applications.show', $interview->application) }}" class="btn btn-sm btn-primary align-self-start">
                            Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="card portal-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Applications</strong>
        <a href="{{ route('candidate.applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>{{ $application->jobPost?->title ?: '-' }}</td>
                        <td>{{ $application->company?->company_name ?: '-' }}</td>
                        <td>{{ $application->statusLabel() }}</td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('candidate.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada lamaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
