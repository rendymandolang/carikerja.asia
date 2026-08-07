@extends('admin.layouts.app')

@section('title', 'Create Company')
@section('page_title', 'Create Company')

@section('content')
<div class="card table-card">
    <div class="card-header bg-white">
        <strong>Company Information</strong>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.companies.store') }}">
            @csrf

            @include('admin.companies._form', [
                'company' => $company,
                'submitLabel' => 'Create Company'
            ])
        </form>
    </div>
</div>
@endsection
