@extends('admin.layouts.app')

@section('title', 'Edit Recruiter')
@section('page_title', 'Edit Recruiter')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Edit: {{ $recruiter->name }}</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.recruiters.update', $recruiter) }}">
            @csrf
            @method('PUT')

            @include('admin.recruiters._form', [
                'recruiter' => $recruiter,
                'companies' => $companies,
                'selectedCompanyId' => $selectedCompanyId,
                'pivotData' => $pivotData,
                'submitLabel' => 'Update Recruiter'
            ])
        </form>
    </div>
</div>
@endsection
