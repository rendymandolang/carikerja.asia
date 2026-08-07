@extends('admin.layouts.app')

@section('title', 'Create Recruiter')
@section('page_title', 'Create Recruiter')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Recruiter Information</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.recruiters.store') }}">
            @csrf

            @include('admin.recruiters._form', [
                'recruiter' => $recruiter,
                'companies' => $companies,
                'selectedCompanyId' => $selectedCompanyId,
                'pivotData' => $pivotData,
                'submitLabel' => 'Create Recruiter'
            ])
        </form>
    </div>
</div>
@endsection
