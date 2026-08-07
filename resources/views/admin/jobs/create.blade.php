@extends('admin.layouts.app')

@section('title', 'Create Job')
@section('page_title', 'Create Job Posting')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Job Information</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.jobs.store') }}">
            @csrf

            @include('admin.jobs._form', [
                'job' => $job,
                'companies' => $companies,
                'submitLabel' => 'Create Job'
            ])
        </form>
    </div>
</div>
@endsection
