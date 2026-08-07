@extends('admin.layouts.app')

@section('title', 'Email Center')
@section('page_title', 'Email Center')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Email Center</h1>
        <div class="text-muted">Kelola email operasional, template otomatis, dan campaign marketing.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-primary">Templates</a>
        <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-primary">New Campaign</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Mailer</div>
                <h3 class="mb-1">{{ $mailDefault }}</h3>
                <span class="badge {{ $mailDefault === 'log' ? 'text-bg-warning' : 'text-bg-success' }}">
                    {{ $mailDefault === 'log' ? 'Log only' : 'Delivery enabled' }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Active Templates</div>
                <h3 class="mb-1">{{ $activeTemplateCount }}/{{ $templateCount }}</h3>
                <span class="text-muted small">System email automation</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Campaigns</div>
                <h3 class="mb-1">{{ $sentCampaignCount }}/{{ $campaignCount }}</h3>
                <span class="text-muted small">Sent / total</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Unsubscribed</div>
                <h3 class="mb-1">{{ $unsubscribedCount }}</h3>
                <span class="text-muted small">Marketing opt-out</span>
            </div>
        </div>
    </div>
</div>

@if ($mailDefault === 'log')
    <div class="alert alert-warning">
        Email production saat ini masih memakai <strong>MAIL_MAILER=log</strong>. Semua email akan masuk ke log server sampai SMTP/Resend/Mailgun dikonfigurasi di file <code>.env</code>.
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Delivery Test</strong>
            </div>
            <div class="card-body">
                <dl class="row small mb-3">
                    <dt class="col-sm-4">From</dt>
                    <dd class="col-sm-8">{{ $mailFromName }} &lt;{{ $mailFromAddress }}&gt;</dd>
                    <dt class="col-sm-4">Mailer</dt>
                    <dd class="col-sm-8">{{ $mailDefault }}</dd>
                </dl>
                <form method="POST" action="{{ route('admin.email.test') }}" class="d-flex gap-2">
                    @csrf
                    <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
                    <button class="btn btn-primary">Send Test</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Audience Preview</strong>
                <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-sm btn-outline-primary">Create Campaign</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>Audience</th>
                        <th class="text-end">Eligible contacts</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($audienceCounts as $audience)
                        <tr>
                            <td>{{ $audience['label'] }}</td>
                            <td class="text-end">{{ $audience['count'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Campaigns</strong>
        <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Name</th>
                <th>Audience</th>
                <th>Status</th>
                <th class="text-end">Sent</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($recentCampaigns as $campaign)
                <tr>
                    <td>
                        <strong>{{ $campaign->name }}</strong>
                        <div class="text-muted small">{{ $campaign->subject }}</div>
                    </td>
                    <td>{{ $campaign->audienceLabel() }}</td>
                    <td><span class="badge {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span></td>
                    <td class="text-end">{{ $campaign->sent_count }}/{{ $campaign->recipient_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada campaign.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
