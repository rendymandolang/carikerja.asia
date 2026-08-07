@extends('recruiter.layouts.app')

@section('title', 'Applications')

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
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Applications</h1>
        <div class="text-muted">Aplikasi kandidat untuk company recruiter Anda.</div>
    </div>
</div>

<div class="card portal-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Candidate, email, job..." value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Job</label>
                <select name="job_post_id" class="form-select">
                    <option value="">All jobs</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}" @selected((string) request('job_post_id') === (string) $job->id)>
                            {{ $job->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card portal-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Messages</th>
                    <th>Applied</th>
                    <th class="text-end">Action</th>
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
                            <div class="text-muted small">{{ $application->company?->company_name ?: '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }}">
                                {{ $application->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            @if ($application->unread_message_count > 0)
                                <span class="badge text-bg-danger">{{ $application->unread_message_count }} unread</span>
                            @else
                                <span class="text-muted small">No new messages</span>
                            @endif
                        </td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y') : '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('recruiter.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada aplikasi kandidat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($applications->hasPages())
        <div class="card-footer bg-white">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
