@extends('admin.layouts.app')

@section('title', 'Company Detail')
@section('page_title', 'Company Detail')

@section('content')
@php
    $statusColors = [
        'pending' => 'warning',
        'active' => 'success',
        'suspended' => 'danger',
        'rejected' => 'secondary',
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $company->company_name }}</strong>
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-primary">Edit Company</a>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Company Name</dt>
                    <dd class="col-sm-8">{{ $company->company_name }}</dd>

                    <dt class="col-sm-4">Legal Name</dt>
                    <dd class="col-sm-8">{{ $company->legal_name ?: '-' }}</dd>

                    <dt class="col-sm-4">Slug</dt>
                    <dd class="col-sm-8">{{ $company->slug }}</dd>

                    <dt class="col-sm-4">Industry</dt>
                    <dd class="col-sm-8">{{ $company->industry ?: '-' }}</dd>

                    <dt class="col-sm-4">Website</dt>
                    <dd class="col-sm-8">
                        @if ($company->website)
                            <a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $company->email ?: '-' }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $company->phone ?: '-' }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">{{ trim(($company->city ?: '') . ', ' . ($company->province ?: ''), ', ') ?: '-' }}</dd>

                    <dt class="col-sm-4">Address</dt>
                    <dd class="col-sm-8">{{ $company->address ?: '-' }}</dd>

                    <dt class="col-sm-4">Notes</dt>
                    <dd class="col-sm-8">{{ $company->notes ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Status</strong>
            </div>

            <div class="card-body">
                <span class="badge bg-{{ $statusColors[$company->status] ?? 'secondary' }} fs-6">
                    {{ ucfirst($company->status) }}
                </span>

                <hr>

                <div class="text-muted small">Created At</div>
                <div>{{ $company->created_at->format('d M Y H:i') }}</div>

                <div class="text-muted small mt-3">Updated At</div>
                <div>{{ $company->updated_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        <a href="{{ route('admin.companies.index') }}" class="btn btn-link mt-3">← Back to Companies</a>
    </div>
</div>
@endsection
