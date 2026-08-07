@extends('admin.layouts.app')

@section('title', 'New Marketing Template')
@section('page_title', 'New Marketing Template')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">New Marketing Template</h1>
        <div class="text-muted">Template reusable untuk informasi, promo, dan campaign.</div>
    </div>
    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.email.templates.store') }}">
                    @csrf
                    @include('admin.email.templates._form')
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @include('admin.email.templates._variables')
    </div>
</div>
@endsection
