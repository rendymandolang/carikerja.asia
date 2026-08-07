@extends('admin.layouts.app')

@section('title', 'Edit Email Template')
@section('page_title', 'Edit Email Template')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $template->name }}</h1>
        <div class="text-muted">{{ $template->key }}</div>
    </div>
    <div class="d-flex gap-2">
        @if ($template->category === 'marketing')
            <a href="{{ route('admin.email.campaigns.create', ['template_id' => $template->id]) }}" class="btn btn-primary">Use for Campaign</a>
        @endif
        <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.email.templates.update', $template) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.email.templates._form')
                </form>
            </div>
        </div>

        @if ($template->category === 'marketing')
            <div class="card table-card mt-4">
                <div class="card-header bg-white"><strong>Danger Zone</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email.templates.destroy', $template) }}" onsubmit="return confirm('Hapus template marketing ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger">Delete Marketing Template</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        @include('admin.email.templates._variables')
    </div>
</div>
@endsection
