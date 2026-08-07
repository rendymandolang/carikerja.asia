@extends('admin.layouts.app')

@section('title', 'Edit Job')
@section('page_title', 'Edit Job Posting')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Edit: {{ $job->title }}</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.jobs.update', $job) }}">
            @csrf
            @method('PUT')

            @include('admin.jobs._form', [
                'job' => $job,
                'companies' => $companies,
                'submitLabel' => 'Update Job'
            ])
        </form>
    </div>
</div>
@endsection
