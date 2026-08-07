@extends('candidate.layouts.app')

@section('title', 'Interviews')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Interviews</h1>
        <div class="text-muted">Jadwal interview dari semua lamaran Anda.</div>
    </div>

    <a href="{{ route('candidate.applications.index') }}" class="btn btn-outline-primary">Applications</a>
</div>

<div class="card portal-card">
    <div class="list-group list-group-flush">
        @forelse ($interviews as $interview)
            @php
                $statusColors = [
                    'scheduled' => 'primary',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
            @endphp

            <div class="list-group-item">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <strong>{{ $interview->title }}</strong>
                            <span class="badge text-bg-{{ $statusColors[$interview->status] ?? 'secondary' }}">
                                {{ $interview->statusLabel() }}
                            </span>
                        </div>

                        <div>{{ $interview->application?->jobPost?->title ?: 'Application' }}</div>
                        <div class="text-muted small">{{ $interview->company?->company_name ?: '-' }}</div>

                        <div class="mt-2">
                            <i class="mdi mdi-calendar-clock me-1"></i>
                            {{ $interview->scheduledAtLabel() }}
                        </div>
                        <div class="text-muted small">
                            {{ $interview->typeLabel() }} - {{ $interview->durationLabel() }}
                        </div>

                        @php($meetingUrl = $interview->meetingUrl())
                        @if ($meetingUrl)
                            <div class="mt-2">
                                <a href="{{ $meetingUrl }}" target="_blank" rel="noopener">
                                    {{ $meetingUrl }}
                                </a>
                            </div>
                        @endif

                        @if ($interview->location)
                            <div class="mt-2">{{ $interview->location }}</div>
                        @endif

                        @if ($interview->notes)
                            <div class="text-muted mt-2" style="white-space: pre-line">{{ $interview->notes }}</div>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('candidate.applications.show', $interview->application) }}" class="btn btn-sm btn-primary">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">
                Belum ada jadwal interview.
            </div>
        @endforelse
    </div>

    @if ($interviews->hasPages())
        <div class="card-footer bg-white">
            {{ $interviews->links() }}
        </div>
    @endif
</div>
@endsection
