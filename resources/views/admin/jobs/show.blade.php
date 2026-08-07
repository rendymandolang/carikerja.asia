@extends('admin.layouts.app')

@section('title', 'Job Detail')
@section('page_title', 'Job Detail')

@section('content')
@php
    $statusColors = [
        'draft' => 'secondary',
        'published' => 'success',
        'closed' => 'danger',
        'archived' => 'dark',
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $job->title }}</strong>
                <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-sm btn-primary">Edit Job</a>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">
                        @if ($job->company)
                            <a href="{{ route('admin.companies.show', $job->company) }}">
                                {{ $job->company->company_name }}
                            </a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Slug</dt>
                    <dd class="col-sm-8">{{ $job->slug }}</dd>

                    <dt class="col-sm-4">Department</dt>
                    <dd class="col-sm-8">{{ $job->department ?: '-' }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">
                        {{ $job->location ?: '-' }}
                        <div class="text-muted small">
                            {{ trim(($job->city ?: '') . ', ' . ($job->province ?: '') . ', ' . ($job->country ?: ''), ', ') }}
                        </div>
                    </dd>

                    <dt class="col-sm-4">Employment Type</dt>
                    <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</dd>

                    <dt class="col-sm-4">Work Arrangement</dt>
                    <dd class="col-sm-8">{{ ucfirst($job->work_arrangement) }}</dd>

                    <dt class="col-sm-4">Salary</dt>
                    <dd class="col-sm-8">{{ $job->salaryRangeLabel() }}</dd>

                    <dt class="col-sm-4">Application Deadline</dt>
                    <dd class="col-sm-8">
                        {{ $job->application_deadline ? $job->application_deadline->format('d M Y') : '-' }}
                    </dd>

                    <dt class="col-sm-4">Created By</dt>
                    <dd class="col-sm-8">{{ $job->createdBy?->name ?: '-' }}</dd>
                </dl>

                <hr>

                <h5>Description</h5>
                <div style="white-space: pre-line">{{ $job->description }}</div>

                @if ($job->requirements)
                    <hr>
                    <h5>Requirements</h5>
                    <div style="white-space: pre-line">{{ $job->requirements }}</div>
                @endif

                @if ($job->benefits)
                    <hr>
                    <h5>Benefits</h5>
                    <div style="white-space: pre-line">{{ $job->benefits }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Status</strong>
            </div>

            <div class="card-body">
                <span class="badge bg-{{ $statusColors[$job->status] ?? 'secondary' }} fs-6">
                    {{ ucfirst($job->status) }}
                </span>

                <hr>

                <div class="text-muted small">Published At</div>
                <div>{{ $job->published_at ? $job->published_at->format('d M Y H:i') : '-' }}</div>

                <div class="text-muted small mt-3">Created At</div>
                <div>{{ $job->created_at->format('d M Y H:i') }}</div>

                <div class="text-muted small mt-3">Updated At</div>
                <div>{{ $job->updated_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        <a href="{{ route('admin.jobs.index') }}" class="btn btn-link mt-3">← Back to Jobs</a>
    </div>
</div>
@endsection
