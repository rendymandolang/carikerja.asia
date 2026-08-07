@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Total Waitlist</div>
                <h2>{{ $totalWaitlists }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Candidates</div>
                <h2>{{ $totalCandidates }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Recruiters</div>
                <h2>{{ $totalRecruiters }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">New Leads</div>
                <h2>{{ $newWaitlists }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Applications</div>
                <h2>{{ $totalApplications }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Submitted</div>
                <h2>{{ $submittedApplications }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Interview</div>
                <h2>{{ $interviewApplications }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted">Hired</div>
                <h2>{{ $hiredApplications }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Applications</strong>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Applied</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentApplications as $application)
                    <tr>
                        <td>
                            <strong>{{ $application->candidateProfile?->full_name ?: '-' }}</strong>
                            <div class="text-muted small">{{ $application->candidateProfile?->email ?: '-' }}</div>
                        </td>
                        <td>{{ $application->jobPost?->title ?: '-' }}</td>
                        <td>{{ $application->company?->company_name ?: '-' }}</td>
                        <td>{{ $application->statusLabel() }}</td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data application.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Waitlists</strong>
        <a href="{{ route('admin.waitlists.index') }}" class="btn btn-sm btn-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name / Company</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentWaitlists as $item)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $item->type === 'candidate' ? 'primary' : 'success' }}">
                                {{ ucfirst($item->type) }}
                            </span>
                        </td>
                        <td>{{ $item->full_name ?: $item->company_name }}</td>
                        <td>{{ $item->email ?: $item->company_email }}</td>
                        <td>{{ ucfirst($item->admin_status) }}</td>
                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data waitlist.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
