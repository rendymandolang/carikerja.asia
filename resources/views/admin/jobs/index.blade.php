@extends('admin.layouts.app')

@section('title', 'Jobs')
@section('page_title', 'Job Posting Management')

@section('content')
@php
    $statusColors = [
        'draft' => 'secondary',
        'published' => 'success',
        'closed' => 'danger',
        'archived' => 'dark',
    ];
@endphp

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title, company, city..." value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archived</option>
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

            <div class="col-md-1 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>

            <div class="col-md-1 d-grid">
                <a href="{{ route('admin.jobs.create') }}" class="btn btn-success">Add</a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Jobs</strong>
        <span class="text-muted small">{{ $jobs->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>
                            <strong>{{ $job->title }}</strong>
                            <div class="text-muted small">{{ $job->department ?: $job->slug }}</div>
                        </td>
                        <td>{{ $job->company?->company_name ?: '-' }}</td>
                        <td>{{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: '-') }}</td>
                        <td>
                            <div>{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</div>
                            <div class="text-muted small">{{ ucfirst($job->work_arrangement) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$job->status] ?? 'secondary' }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td>{{ $job->application_deadline ? $job->application_deadline->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada job posting.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $jobs->links() }}
    </div>
</div>
@endsection
