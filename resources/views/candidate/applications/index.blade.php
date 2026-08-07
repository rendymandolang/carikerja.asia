@extends('candidate.layouts.app')

@section('title', 'Applications')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Lamaran Saya</h1>
        <div class="text-muted">Pantau status lamaran yang sudah Anda kirim.</div>
    </div>

    <a href="{{ route('jobs.index') }}" class="btn btn-primary">Apply More Jobs</a>
</div>

<div class="card portal-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Messages</th>
                    <th>Stage</th>
                    <th>Applied</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>{{ $application->jobPost?->title ?: '-' }}</td>
                        <td>{{ $application->company?->company_name ?: '-' }}</td>
                        <td>{{ $application->statusLabel() }}</td>
                        <td>
                            @if ($application->unread_message_count > 0)
                                <span class="badge text-bg-danger">{{ $application->unread_message_count }} unread</span>
                            @else
                                <span class="text-muted small">No new messages</span>
                            @endif
                        </td>
                        <td>{{ $application->current_stage ?: '-' }}</td>
                        <td>{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('candidate.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada lamaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $applications->links() }}
    </div>
</div>
@endsection
