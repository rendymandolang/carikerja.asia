@extends('admin.layouts.app')

@section('title', 'New Email Campaign')
@section('page_title', 'New Email Campaign')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">New Campaign</h1>
        <div class="text-muted">Buat email informasi, promosi, atau update marketing.</div>
    </div>
    <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card table-card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.email.campaigns.store') }}">
            @include('admin.email.campaigns._form')
        </form>
    </div>
</div>
@endsection
