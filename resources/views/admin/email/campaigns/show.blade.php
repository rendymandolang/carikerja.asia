@extends('admin.layouts.app')

@section('title', 'Email Campaign Detail')
@section('page_title', 'Email Campaign Detail')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $campaign->name }}</h1>
        <div class="text-muted">{{ $campaign->subject }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-outline-secondary">Back</a>
        @if ($campaign->isEditable())
            <a href="{{ route('admin.email.campaigns.edit', $campaign) }}" class="btn btn-outline-primary">Edit</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body"><div class="text-muted">Status</div><h4><span class="badge {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span></h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body"><div class="text-muted">Audience</div><h4>{{ $audienceCount }}</h4><div class="text-muted small">eligible now</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body"><div class="text-muted">Sent</div><h4>{{ $campaign->sent_count }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body"><div class="text-muted">Failed / Skipped</div><h4>{{ $campaign->failed_count }} / {{ $campaign->skipped_count }}</h4></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white"><strong>Preview</strong></div>
            <div class="card-body">
                <dl class="row small">
                    <dt class="col-sm-3">Audience</dt>
                    <dd class="col-sm-9">{{ $campaign->audienceLabel() }}</dd>
                    <dt class="col-sm-3">Template</dt>
                    <dd class="col-sm-9">{{ $campaign->template?->name ?: 'Custom campaign' }}</dd>
                    <dt class="col-sm-3">Subject</dt>
                    <dd class="col-sm-9">{{ $campaign->subject }}</dd>
                    <dt class="col-sm-3">Preheader</dt>
                    <dd class="col-sm-9">{{ $campaign->preheader ?: '-' }}</dd>
                    <dt class="col-sm-3">Scheduled</dt>
                    <dd class="col-sm-9">{{ $campaign->scheduled_at?->format('d M Y H:i') ?: '-' }}</dd>
                    <dt class="col-sm-3">Queued</dt>
                    <dd class="col-sm-9">{{ $campaign->queued_at?->format('d M Y H:i') ?: '-' }}</dd>
                    <dt class="col-sm-3">Finished</dt>
                    <dd class="col-sm-9">{{ $campaign->finished_at?->format('d M Y H:i') ?: '-' }}</dd>
                </dl>
                @if ($campaign->last_error)
                    <div class="alert alert-warning">{{ $campaign->last_error }}</div>
                @endif
                <hr>
                <div class="preline">{{ $campaign->body }}</div>
                @if ($campaign->button_label && $campaign->button_url)
                    <div class="mt-3">
                        <a href="{{ $campaign->button_url }}" class="btn btn-primary" target="_blank">{{ $campaign->button_label }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card table-card mb-4">
            <div class="card-header bg-white"><strong>Send Test</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.email.campaigns.test', $campaign) }}" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-primary">Send Test</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($campaign->isEditable())
            <div class="card table-card">
                <div class="card-header bg-white"><strong>Schedule</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email.campaigns.schedule', $campaign) }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Scheduled At</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary">Schedule Campaign</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($campaign->canCancelSchedule())
            <div class="card table-card mt-4">
                <div class="card-header bg-white"><strong>Cancel Schedule</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email.campaigns.cancel-schedule', $campaign) }}">
                        @csrf
                        <button class="btn btn-outline-danger">Cancel Schedule</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($campaign->canQueueSend())
            <div class="card table-card mt-4">
                <div class="card-header bg-white"><strong>Send Now</strong></div>
                <div class="card-body">
                    <p class="text-muted">Campaign akan masuk queue dan otomatis skip kontak yang sudah unsubscribe.</p>
                    <form method="POST" action="{{ route('admin.email.campaigns.send', $campaign) }}" onsubmit="return confirm('Kirim campaign ini sekarang?')">
                        @csrf
                        <button class="btn btn-danger">Queue Campaign</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="card table-card mt-4">
    <div class="card-header bg-white"><strong>Recipients</strong></div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Email</th>
                <th>Name</th>
                <th>Source</th>
                <th>Status</th>
                <th>Sent At</th>
                <th>Failure</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($recipients as $recipient)
                <tr>
                    <td>{{ $recipient->email }}</td>
                    <td>{{ $recipient->name ?: '-' }}</td>
                    <td>{{ $recipient->source_type ?: '-' }} #{{ $recipient->source_id ?: '-' }}</td>
                    <td><span class="badge {{ $recipient->status === 'sent' ? 'text-bg-success' : ($recipient->status === 'failed' ? 'text-bg-danger' : 'text-bg-secondary') }}">{{ $recipient->status }}</span></td>
                    <td>{{ $recipient->sent_at?->format('d M Y H:i') ?: '-' }}</td>
                    <td class="small text-muted">{{ $recipient->failure_reason ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Recipient log akan muncul setelah campaign dikirim.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $recipients->links() }}</div>
@endsection
