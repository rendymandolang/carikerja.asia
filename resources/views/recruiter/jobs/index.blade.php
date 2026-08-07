@extends('recruiter.layouts.app')

@section('title', 'Jobs')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Jobs</h1>
        <div class="text-muted">Lowongan yang terhubung dengan company Anda.</div>
    </div>

    <a href="{{ route('recruiter.jobs.create') }}" class="btn btn-primary">Create Job</a>
</div>

<div class="card portal-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title, department, location..." value="{{ request('q') }}">
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
                    <th>Job</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Deadline</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>
                            <strong>{{ $job->title }}</strong>
                            <div class="text-muted small">{{ $job->department ?: '-' }}</div>
                        </td>
                        <td>{{ $job->company?->company_name ?: '-' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $job->status)) }}</td>
                        <td>{{ $job->applications_count }}</td>
                        <td>{{ $job->application_deadline ? $job->application_deadline->format('d M Y') : 'Open until filled' }}</td>
                        <td class="text-end">
                            <a href="{{ route('recruiter.jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            <a href="{{ route('recruiter.jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada job untuk company recruiter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($jobs->hasPages())
        <div class="card-footer bg-white">
            {{ $jobs->links() }}
        </div>
    @endif
</div>
@endsection
