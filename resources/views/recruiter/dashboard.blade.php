@extends('recruiter.layouts.app')

@section('title', 'Recruiter Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Recruiter Dashboard</h1>
        <div class="text-muted">
            {{ $companies->pluck('company_name')->join(', ') ?: 'Belum ada company aktif' }}
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('recruiter.jobs.create') }}" class="btn btn-outline-secondary">Create Job</a>
        <a href="{{ route('recruiter.jobs.index') }}" class="btn btn-outline-primary">View Jobs</a>
        <a href="{{ route('recruiter.applications.index') }}" class="btn btn-primary">View Applications</a>
    </div>
</div>

@if ($overdueApplications || $jobsDueConfirmation)
    <div class="alert alert-warning d-flex flex-wrap justify-content-between gap-2">
        <span><strong>Hire tanpa ghosting:</strong> {{ $overdueApplications }} lamaran terlambat direspons dan {{ $jobsDueConfirmation }} lowongan perlu dikonfirmasi dalam 7 hari.</span>
        <a href="{{ route('recruiter.applications.index') }}" class="alert-link">Tindak lanjuti sekarang</a>
    </div>
@endif

@if ($companies->isNotEmpty())
    <div class="row g-3 mb-4">
        @foreach ($companies as $company)
            <div class="col-md-6"><div class="card portal-card"><div class="card-body">
                <div class="d-flex justify-content-between"><strong>{{ $company->company_name }}</strong><div>@if($company->is_verified)<span class="badge bg-success">Terverifikasi</span>@endif @if($company->isActiveResponder())<span class="badge bg-info text-dark">Aktif merespons</span>@endif</div></div>
                <div class="text-muted mt-2">Response rate {{ $company->response_sample_size ? number_format((float) $company->response_rate, 0).'%' : 'belum ada data' }} · median {{ $company->median_response_hours !== null ? number_format((float) $company->median_response_hours, 1).' jam' : '-' }}</div>
            </div></div></div>
        @endforeach
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Total Jobs</div>
                <div class="stat-value">{{ $totalJobs }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Open Jobs</div>
                <div class="stat-value">{{ $openJobs }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Applications</div>
                <div class="stat-value">{{ $totalApplications }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card portal-card">
            <div class="card-body">
                <div class="text-muted">Interviews</div>
                <div class="stat-value">{{ $interviewApplications }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Applications</strong>
        <a href="{{ route('recruiter.applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentApplications as $application)
                    <tr>
                        <td>
                            <strong>{{ $application->candidateProfile?->full_name ?: '-' }}</strong>
                            <div class="text-muted small">{{ $application->candidateProfile?->email ?: '-' }}</div>
                        </td>
                        <td>
                            {{ $application->jobPost?->title ?: '-' }}
                            <div class="text-muted small">{{ $application->company?->company_name ?: '-' }}</div>
                        </td>
                        <td>{{ $application->statusLabel() }}</td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y') : '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('recruiter.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada aplikasi kandidat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
