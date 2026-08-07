@extends('candidate.layouts.app')

@section('title', 'Application Detail')

@section('content')
@php
    $statusColors = [
        'submitted' => 'secondary',
        'screening' => 'info',
        'shortlisted' => 'primary',
        'interview' => 'warning',
        'offer' => 'success',
        'hired' => 'success',
        'rejected' => 'danger',
        'withdrawn' => 'dark',
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card portal-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $application->jobPost?->title ?: 'Application' }}</strong>
                <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }}">
                    {{ $application->statusLabel() }}
                </span>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">{{ $application->company?->company_name ?: '-' }}</dd>

                    <dt class="col-sm-4">Current Stage</dt>
                    <dd class="col-sm-8">{{ $application->current_stage ?: '-' }}</dd>

                    <dt class="col-sm-4">Applied At</dt>
                    <dd class="col-sm-8">{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</dd>

                    <dt class="col-sm-4">Last Status Change</dt>
                    <dd class="col-sm-8">{{ $application->last_status_changed_at ? $application->last_status_changed_at->format('d M Y H:i') : '-' }}</dd>
                </dl>

                @if ($application->cover_letter)
                    <hr>
                    <h5>Cover Letter</h5>
                    <div style="white-space: pre-line">{{ $application->cover_letter }}</div>
                @endif
            </div>
        </div>

        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Interview Schedule</strong>
            </div>

            <div class="card-body">
                @forelse ($application->interviews as $interview)
                    @php
                        $interviewStatusColors = [
                            'scheduled' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        ];
                    @endphp

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <strong>{{ $interview->title }}</strong>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge text-bg-{{ $interviewStatusColors[$interview->status] ?? 'secondary' }}">
                                    {{ $interview->statusLabel() }}
                                </span>
                                @if ($interview->google_sync_status === 'synced')
                                    <span class="badge text-bg-success">Google Meet</span>
                                @endif
                            </div>
                        </div>
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
                @empty
                    <div class="text-muted">Belum ada jadwal interview.</div>
                @endforelse
            </div>
        </div>

        <div class="card portal-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Messages</strong>
                <span class="text-muted small">{{ $application->messages->count() }} message(s)</span>
            </div>

            <div class="card-body">
                <div class="mb-4" style="max-height: 420px; overflow-y: auto;">
                    @forelse ($application->messages as $message)
                        @php($isMine = $message->isFromCandidate())
                        <div class="d-flex mb-3 {{ $isMine ? 'justify-content-end' : '' }}">
                            <div class="p-3 rounded-3 {{ $isMine ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 82%;">
                                <div class="d-flex flex-wrap gap-2 justify-content-between small {{ $isMine ? 'text-white-50' : 'text-muted' }}">
                                    <span>{{ $message->sender?->name ?: $message->senderLabel() }}</span>
                                    <span>{{ $message->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="mt-2" style="white-space: pre-line">{{ $message->body }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada pesan. Anda bisa mengirim pertanyaan atau konfirmasi kepada recruiter dari sini.</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('candidate.applications.messages.store', $application) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Reply Message</label>
                        <textarea name="body" rows="4" class="form-control" maxlength="3000" required placeholder="Tulis pesan untuk recruiter...">{{ old('body') }}</textarea>
                        @error('body') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card portal-card">
            <div class="card-header bg-white">
                <strong>Status Timeline</strong>
            </div>

            <div class="card-body">
                @forelse ($application->statusHistories as $history)
                    <div class="mb-3 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-{{ $statusColors[$history->to_status] ?? 'primary' }}">
                                {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                            </span>
                        </div>
                        <div class="text-muted small mt-1">{{ $history->changed_at->format('d M Y H:i') }}</div>
                        @if ($history->notes)
                            <div class="mt-2" style="white-space: pre-line">{{ $history->notes }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted">Belum ada histori status.</div>
                @endforelse
            </div>
        </div>

        <a href="{{ route('candidate.applications.index') }}" class="btn btn-link mt-3">&larr; Back to Applications</a>
    </div>
</div>
@endsection
