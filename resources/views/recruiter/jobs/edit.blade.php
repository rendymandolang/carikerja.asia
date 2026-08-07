@extends('recruiter.layouts.app')

@section('title', 'Edit Job')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Edit Job</h1>
        <div class="text-muted">{{ $job->title }}</div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('recruiter.jobs.update', $job) }}">
            @csrf
            @method('PUT')
            @include('recruiter.jobs._form', [
                'job' => $job,
                'companies' => $companies,
                'submitLabel' => 'Update Job',
            ])
        </form>
    </div>
</div>
@endsection
