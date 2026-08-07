@extends('recruiter.layouts.app')

@section('title', 'Create Job')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Create Job</h1>
        <div class="text-muted">Buat lowongan baru untuk company Anda.</div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('recruiter.jobs.store') }}">
            @csrf
            @include('recruiter.jobs._form', [
                'job' => $job,
                'companies' => $companies,
                'submitLabel' => 'Create Job',
            ])
        </form>
    </div>
</div>
@endsection
