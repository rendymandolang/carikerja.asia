@extends('admin.layouts.app')

@section('title', 'Recruiters')
@section('page_title', 'Recruiter Management')

@section('content')
@php
    $statusColors = [
        'active' => 'success',
        'inactive' => 'secondary',
        'suspended' => 'danger',
    ];
@endphp

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Name, email, phone..." value="{{ request('q') }}">
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
                <select name="account_status" class="form-select">
                    <option value="">All</option>
                    <option value="active" @selected(request('account_status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('account_status') === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(request('account_status') === 'suspended')>Suspended</option>
                </select>
            </div>

            <div class="col-md-1 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>

            <div class="col-md-2 d-grid">
                <a href="{{ route('admin.recruiters.create') }}" class="btn btn-success">Add Recruiter</a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recruiters</strong>
        <span class="text-muted small">{{ $recruiters->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Recruiter</th>
                    <th>Company</th>
                    <th>Job Title</th>
                    <th>Company Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recruiters as $recruiter)
                    @php
                        $company = $recruiter->companies->first();
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $recruiter->name }}</strong>
                            <div class="text-muted small">{{ $recruiter->email }}</div>
                            @if ($recruiter->phone)
                                <div class="text-muted small">{{ $recruiter->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $company?->company_name ?: '-' }}</td>
                        <td>{{ $company?->pivot?->job_title ?: '-' }}</td>
                        <td>{{ ucfirst($company?->pivot?->company_role ?: '-') }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$recruiter->account_status] ?? 'secondary' }}">
                                {{ ucfirst($recruiter->account_status) }}
                            </span>
                        </td>
                        <td>{{ $recruiter->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.recruiters.show', $recruiter) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            <a href="{{ route('admin.recruiters.edit', $recruiter) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada recruiter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $recruiters->links() }}
    </div>
</div>
@endsection
