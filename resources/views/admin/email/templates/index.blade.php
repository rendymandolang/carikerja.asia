@extends('admin.layouts.app')

@section('title', 'Email Templates')
@section('page_title', 'Email Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Email Templates</h1>
        <div class="text-muted">Edit subject, body, dan CTA untuk email otomatis sistem.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('admin.email.templates.create') }}" class="btn btn-primary">New Marketing Template</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Template</th>
                <th>Category</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Updated</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($templates as $template)
                <tr>
                    <td>
                        <strong>{{ $template->name }}</strong>
                        <div class="text-muted small">{{ $template->key }}</div>
                    </td>
                    <td>{{ ucfirst($template->category) }}</td>
                    <td>{{ $template->subject }}</td>
                    <td>
                        <span class="badge {{ $template->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="small text-muted">
                        {{ $template->updated_at?->format('d M Y H:i') }}
                        @if ($template->updatedBy)
                            <br>by {{ $template->updatedBy->name }}
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if ($template->category === 'marketing')
                                <a href="{{ route('admin.email.campaigns.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-outline-primary">Use</a>
                            @endif
                            <a href="{{ route('admin.email.templates.edit', $template) }}" class="btn btn-sm btn-primary">Edit</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $templates->links() }}</div>
@endsection
