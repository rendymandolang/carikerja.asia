@extends('admin.layouts.app')

@section('title', 'Companies')
@section('page_title', 'Company Management')

@section('content')
@php
    $statusColors = [
        'pending' => 'warning',
        'active' => 'success',
        'suspended' => 'danger',
        'rejected' => 'secondary',
    ];
@endphp

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Company, email, city, industry..." value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>

            <div class="col-md-2 d-grid">
                <a href="{{ route('admin.companies.create') }}" class="btn btn-success">Add Company</a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Companies</strong>
        <span class="text-muted small">{{ $companies->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Industry</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                            <div class="text-muted small">{{ $company->legal_name ?: $company->slug }}</div>
                        </td>
                        <td>{{ $company->industry ?: '-' }}</td>
                        <td>{{ $company->email ?: '-' }}</td>
                        <td>{{ trim(($company->city ?: '') . ', ' . ($company->province ?: ''), ', ') ?: '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$company->status] ?? 'secondary' }}">
                                {{ ucfirst($company->status) }}
                            </span>
                        </td>
                        <td>{{ $company->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada company.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $companies->links() }}
    </div>
</div>
@endsection
