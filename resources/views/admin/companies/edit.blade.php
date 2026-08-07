@extends('admin.layouts.app')

@section('title', 'Edit Company')
@section('page_title', 'Edit Company')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Edit: {{ $company->company_name }}</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.companies.update', $company) }}">
            @csrf
            @method('PUT')

            @include('admin.companies._form', [
                'company' => $company,
                'submitLabel' => 'Update Company'
            ])
        </form>
    </div>
</div>
@endsection
