@extends('admin.layouts.app')

@section('title', 'Operations')
@section('page_title', 'Operations')

@section('content')
    @php
        $badgeClass = [
            'ok' => 'bg-success',
            'warning' => 'bg-warning text-dark',
            'critical' => 'bg-danger',
            'running' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
        ];
        $statusIcon = [
            'ok' => 'mdi-check-circle-outline',
            'warning' => 'mdi-alert-circle-outline',
            'critical' => 'mdi-close-circle-outline',
        ];
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">System Operations</h1>
            <div class="text-muted">Health, queue, scheduler, and backup readiness.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.system.queue.run-once') }}">
                @csrf
                <button class="btn btn-outline-primary">
                    <i class="mdi mdi-play-circle-outline"></i> Run Queue
                </button>
            </form>
            <form method="POST" action="{{ route('admin.system.backups.run') }}">
                @csrf
                <input type="hidden" name="type" value="full">
                <button class="btn btn-primary">
                    <i class="mdi mdi-database-arrow-down-outline"></i> Full Backup
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Overall</div>
                    <div class="h4 mb-0 text-capitalize">{{ $summary['overall'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">OK</div>
                    <div class="h4 mb-0 text-success">{{ $summary['ok'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Warning</div>
                    <div class="h4 mb-0 text-warning">{{ $summary['warning'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Critical</div>
                    <div class="h4 mb-0 text-danger">{{ $summary['critical'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card table-card h-100">
                <div class="card-header bg-white">
                    <strong>Health Checks</strong>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($checks as $check)
                        <div class="list-group-item d-flex align-items-start justify-content-between gap-3">
                            <div class="d-flex gap-3">
                                <i class="mdi {{ $statusIcon[$check['status']] ?? 'mdi-circle-outline' }} fs-4 text-{{ $check['status'] === 'ok' ? 'success' : ($check['status'] === 'warning' ? 'warning' : 'danger') }}"></i>
                                <div>
                                    <div class="fw-semibold">{{ $check['label'] }}</div>
                                    <div class="text-muted small">{{ $check['message'] }}</div>
                                </div>
                            </div>
                            <span class="badge {{ $badgeClass[$check['status']] ?? 'bg-secondary' }}">{{ $check['status'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card table-card h-100">
                <div class="card-header bg-white">
                    <strong>Platform Settings</strong>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <tbody>
                            @foreach ($settings as $setting)
                                <tr>
                                    <th class="text-muted small">{{ $setting['label'] }}</th>
                                    <td class="text-end small">{{ $setting['value'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card table-card h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <strong>Mail & SMTP Readiness</strong>
                    <span class="badge {{ $badgeClass[$mailReadiness['summary']['overall']] ?? 'bg-secondary' }}">
                        {{ $mailReadiness['summary']['overall'] }}
                    </span>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($mailReadiness['checks'] as $check)
                        <div class="list-group-item d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $check['label'] }}</div>
                                <div class="text-muted small">{{ $check['message'] }}</div>
                            </div>
                            <span class="badge {{ $badgeClass[$check['status']] ?? 'bg-secondary' }}">{{ $check['status'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card table-card h-100">
                <div class="card-header bg-white">
                    <strong>Mail Settings</strong>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <tbody>
                            @foreach ($mailReadiness['settings'] as $setting)
                                <tr>
                                    <th class="text-muted small">{{ $setting['label'] }}</th>
                                    <td class="text-end small">{{ $setting['value'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card table-card h-100">
                <div class="card-header bg-white">
                    <strong>Queue & Scheduler</strong>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Connection</span>
                        <strong>{{ $queueMetrics['connection'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Pending Jobs</span>
                        <strong>{{ $queueMetrics['pending_jobs'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Failed Jobs</span>
                        <strong>{{ $queueMetrics['failed_jobs'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Oldest Job</span>
                        <strong>{{ $queueMetrics['oldest_job_at']?->diffForHumans() ?? 'None' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="text-muted">Scheduler</span>
                        <strong>{{ $schedulerHeartbeat?->last_ping_at?->diffForHumans() ?? 'No heartbeat' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card table-card h-100">
                <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <strong>Backup Core</strong>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($backupTypes as $type)
                            <form method="POST" action="{{ route('admin.system.backups.run') }}">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button class="btn btn-sm {{ $type === 'full' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    {{ ucfirst($type) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Size</th>
                                <th>Finished</th>
                                <th>Path</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backupRuns as $backup)
                                <tr>
                                    <td class="text-capitalize">{{ $backup->type }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass[$backup->status] ?? 'bg-secondary' }}">
                                            {{ $backup->status }}
                                        </span>
                                        @if ($backup->error_message)
                                            <div class="small text-danger mt-1">{{ $backup->error_message }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $backup->sizeLabel() }}</td>
                                    <td>{{ $backup->finished_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="small text-muted">
                                        @if ($backup->path)
                                            storage/app/private/{{ $backup->path }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="small">{{ $backup->triggeredBy?->email ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No backup runs yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
