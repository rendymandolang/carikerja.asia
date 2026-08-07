@extends('recruiter.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Notifications</h1>
        <div class="text-muted">Update aplikasi kandidat dan aktivitas hiring company Anda.</div>
    </div>

    <form method="POST" action="{{ route('recruiter.notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-primary">Mark All Read</button>
    </form>
</div>

<div class="card portal-card">
    <div class="list-group list-group-flush">
        @forelse ($notifications as $notification)
            @php($data = $notification->data)
            <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                        @if (! $notification->read_at)
                            <span class="badge text-bg-primary ms-2">New</span>
                        @endif
                        <div class="text-muted mt-1">{{ $data['body'] ?? '-' }}</div>
                        <div class="text-muted small mt-1">{{ $notification->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="d-flex gap-2 align-items-start">
                        @if (! empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-primary">{{ $data['action_label'] ?? 'Open' }}</a>
                        @endif
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('recruiter.notifications.read', $notification->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Mark Read</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">Belum ada notifikasi.</div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
