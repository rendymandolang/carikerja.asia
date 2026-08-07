@extends('admin.layouts.app')

@section('title', 'Email Campaigns')
@section('page_title', 'Email Campaigns')

@section('content')
<div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Email Campaigns</h1>
        <div class="text-muted">Campaign informasi dan marketing untuk user dan waitlist.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-primary">New Campaign</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Campaign</th>
                <th>Audience</th>
                <th>Status</th>
                <th>Sent</th>
                <th>Created</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($campaigns as $campaign)
                <tr>
                    <td>
                        <strong>{{ $campaign->name }}</strong>
                        <div class="text-muted small">{{ $campaign->subject }}</div>
                        @if ($campaign->template)
                            <div class="text-muted small">Template: {{ $campaign->template->name }}</div>
                        @endif
                    </td>
                    <td>{{ $campaign->audienceLabel() }}</td>
                    <td>
                        <span class="badge {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                        @if ($campaign->scheduled_at && $campaign->status === 'scheduled')
                            <div class="small text-muted">{{ $campaign->scheduled_at->format('d M Y H:i') }}</div>
                        @endif
                    </td>
                    <td>{{ $campaign->sent_count }}/{{ $campaign->recipient_count }}</td>
                    <td class="small text-muted">
                        {{ $campaign->created_at?->format('d M Y H:i') }}
                        @if ($campaign->createdBy)
                            <br>by {{ $campaign->createdBy->name }}
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada campaign.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $campaigns->links() }}</div>
@endsection
