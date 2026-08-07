@extends('admin.layouts.app')

@section('title', 'Recruiter Detail')
@section('page_title', 'Recruiter Detail')

@section('content')
@php
    $statusColors = [
        'active' => 'success',
        'inactive' => 'secondary',
        'suspended' => 'danger',
    ];

    $company = $recruiter->companies->first();
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $recruiter->name }}</strong>
                <a href="{{ route('admin.recruiters.edit', $recruiter) }}" class="btn btn-sm btn-primary">Edit Recruiter</a>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $recruiter->name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $recruiter->email }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $recruiter->phone ?: '-' }}</dd>

                    <dt class="col-sm-4">Role</dt>
                    <dd class="col-sm-8">{{ ucfirst($recruiter->role) }}</dd>

                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">
                        @if ($company)
                            <a href="{{ route('admin.companies.show', $company) }}">
                                {{ $company->company_name }}
                            </a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Company Role</dt>
                    <dd class="col-sm-8">{{ ucfirst($company?->pivot?->company_role ?: '-') }}</dd>

                    <dt class="col-sm-4">Job Title</dt>
                    <dd class="col-sm-8">{{ $company?->pivot?->job_title ?: '-' }}</dd>

                    <dt class="col-sm-4">Company Access</dt>
                    <dd class="col-sm-8">{{ ucfirst($company?->pivot?->status ?: '-') }}</dd>

                    <dt class="col-sm-4">Invited At</dt>
                    <dd class="col-sm-8">
                        {{ $company?->pivot?->invited_at ? \Illuminate\Support\Carbon::parse($company->pivot->invited_at)->format('d M Y H:i') : '-' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Account Status</strong>
            </div>

            <div class="card-body">
                <span class="badge bg-{{ $statusColors[$recruiter->account_status] ?? 'secondary' }} fs-6">
                    {{ ucfirst($recruiter->account_status) }}
                </span>

                <hr>

                <div class="text-muted small">Created At</div>
                <div>{{ $recruiter->created_at->format('d M Y H:i') }}</div>

                <div class="text-muted small mt-3">Updated At</div>
                <div>{{ $recruiter->updated_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        <a href="{{ route('admin.recruiters.index') }}" class="btn btn-link mt-3">← Back to Recruiters</a>
    </div>
</div>
@endsection
