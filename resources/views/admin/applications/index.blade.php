@extends('admin.layouts.app')

@section('title', 'Applications')
@section('page_title', 'Application Management')

@section('content')
@php
    $statusColors = [
        'submitted' => 'secondary',
        'screening' => 'info',
        'shortlisted' => 'primary',
        'interview' => 'warning',
        'offer' => 'success',
        'hired' => 'success',
        'rejected' => 'danger',
        'withdrawn' => 'dark',
    ];

    $sources = [
        'public_job' => 'Public Job',
        'waitlist' => 'Waitlist',
        'admin' => 'Admin',
        'recruiter' => 'Recruiter',
    ];
@endphp

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Candidate, email, job..." value="{{ request('q') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">All Companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) request('company_id') === (string) $company->id)>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Job</label>
                <select name="job_post_id" class="form-select">
                    <option value="">All Jobs</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}" @selected((string) request('job_post_id') === (string) $job->id)>
                            {{ $job->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Source</label>
                <select name="source" class="form-select">
                    <option value="">All</option>
                    @foreach ($sources as $source => $label)
                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Applications</strong>
        <span class="text-muted small">{{ $applications->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>
                            <strong>{{ $application->candidateProfile?->full_name ?: '-' }}</strong>
                            <div class="text-muted small">{{ $application->candidateProfile?->email ?: '-' }}</div>
                        </td>
                        <td>
                            {{ $application->jobPost?->title ?: '-' }}
                            <div class="text-muted small">{{ $application->current_stage ?: 'No stage set' }}</div>
                        </td>
                        <td>{{ $application->company?->company_name ?: '-' }}</td>
                        <td>{{ $sources[$application->source] ?? ucfirst($application->source) }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }}">
                                {{ $application->statusLabel() }}
                            </span>
                        </td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada application.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $applications->links() }}
    </div>
</div>
@endsection
