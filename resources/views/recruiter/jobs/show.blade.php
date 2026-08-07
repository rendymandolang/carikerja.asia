@extends('recruiter.layouts.app')

@section('title', $job->title)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card portal-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $job->title }}</strong>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                    <a href="{{ route('recruiter.jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                </div>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">{{ $job->company?->company_name ?: '-' }}</dd>

                    <dt class="col-sm-4">Department</dt>
                    <dd class="col-sm-8">{{ $job->department ?: '-' }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">{{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: '-') }}</dd>

                    <dt class="col-sm-4">Employment Type</dt>
                    <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</dd>

                    <dt class="col-sm-4">Work Arrangement</dt>
                    <dd class="col-sm-8">{{ ucfirst($job->work_arrangement) }}</dd>

                    <dt class="col-sm-4">Salary</dt>
                    <dd class="col-sm-8">{{ $job->salaryRangeLabel() }}</dd>

                    <dt class="col-sm-4">Deadline</dt>
                    <dd class="col-sm-8">{{ $job->application_deadline ? $job->application_deadline->format('d M Y') : 'Open until filled' }}</dd>
                </dl>

                <hr>
                <h5>Description</h5>
                <div class="mb-4" style="white-space: pre-line">{{ $job->description }}</div>

                @if ($job->requirements)
                    <h5>Requirements</h5>
                    <div class="mb-4" style="white-space: pre-line">{{ $job->requirements }}</div>
                @endif

                @if ($job->benefits)
                    <h5>Benefits</h5>
                    <div style="white-space: pre-line">{{ $job->benefits }}</div>
                @endif
            </div>
        </div>

        <a href="{{ route('recruiter.jobs.index') }}" class="btn btn-link">&larr; Back to Jobs</a>
    </div>

    <div class="col-lg-4">
        <div class="card portal-card">
            <div class="card-header bg-white">
                <strong>Applications</strong>
            </div>

            <div class="list-group list-group-flush">
                @forelse ($applications as $application)
                    <a href="{{ route('recruiter.applications.show', $application) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between gap-2">
                            <strong>{{ $application->candidateProfile?->full_name ?: '-' }}</strong>
                            <span class="badge bg-secondary">{{ $application->statusLabel() }}</span>
                        </div>
                        <div class="text-muted small">{{ $application->candidateProfile?->email ?: '-' }}</div>
                    </a>
                @empty
                    <div class="list-group-item text-muted">Belum ada aplikasi untuk job ini.</div>
                @endforelse
            </div>

            @if ($applications->hasPages())
                <div class="card-footer bg-white">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
