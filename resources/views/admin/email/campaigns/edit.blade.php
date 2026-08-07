@extends('admin.layouts.app')

@section('title', 'Edit Email Campaign')
@section('page_title', 'Edit Email Campaign')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Edit Campaign</h1>
        <div class="text-muted">{{ $campaign->name }}</div>
    </div>
    <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card table-card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.email.campaigns.update', $campaign) }}">
            @include('admin.email.campaigns._form')
        </form>
    </div>
</div>
@endsection
