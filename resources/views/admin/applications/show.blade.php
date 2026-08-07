@extends('admin.layouts.app')

@section('title', 'Application Detail')
@section('page_title', 'Application Detail')

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

    $sources = [
        'public_job' => 'Public Job',
        'waitlist' => 'Waitlist',
        'admin' => 'Admin',
        'recruiter' => 'Recruiter',
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $application->candidateProfile?->full_name ?: 'Candidate' }}</strong>
                <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }} fs-6">
                    {{ $application->statusLabel() }}
                </span>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Candidate Email</dt>
                    <dd class="col-sm-8">{{ $application->candidateProfile?->email ?: '-' }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $application->candidateProfile?->phone ?: '-' }}</dd>

                    <dt class="col-sm-4">LinkedIn</dt>
                    <dd class="col-sm-8">
                        @if ($application->candidateProfile?->linkedin_url)
                            <a href="{{ $application->candidateProfile->linkedin_url }}" target="_blank">{{ $application->candidateProfile->linkedin_url }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Indeed</dt>
                    <dd class="col-sm-8">
                        @if ($application->candidateProfile?->indeed_url)
                            <a href="{{ $application->candidateProfile->indeed_url }}" target="_blank">{{ $application->candidateProfile->indeed_url }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Portfolio</dt>
                    <dd class="col-sm-8">
                        @if ($application->candidateProfile?->portfolio_url)
                            <a href="{{ $application->candidateProfile->portfolio_url }}" target="_blank">{{ $application->candidateProfile->portfolio_url }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Headline</dt>
                    <dd class="col-sm-8">{{ $application->candidateProfile?->headline ?: '-' }}</dd>

                    <dt class="col-sm-4">Current Position</dt>
                    <dd class="col-sm-8">{{ $application->candidateProfile?->current_position ?: '-' }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">
                        {{ $application->candidateProfile?->location ?: '-' }}
                        <div class="text-muted small">
                            {{ trim(($application->candidateProfile?->city ?: '') . ', ' . ($application->candidateProfile?->province ?: '') . ', ' . ($application->candidateProfile?->country ?: ''), ', ') }}
                        </div>
                    </dd>

                    <dt class="col-sm-4">Expected Salary</dt>
                    <dd class="col-sm-8">{{ $application->candidateProfile?->expectedSalaryRangeLabel() ?: '-' }}</dd>

                    <dt class="col-sm-4">Availability</dt>
                    <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $application->candidateProfile?->availability_status ?: '-')) }}</dd>

                    <dt class="col-sm-4">Resume</dt>
                    <dd class="col-sm-8">
                        @if ($application->resume_path || $application->candidateProfile?->resume_path)
                            <a href="{{ route('admin.applications.resume', $application) }}" class="btn btn-sm btn-outline-primary">
                                Download Resume
                            </a>
                        @else
                            -
                        @endif
                    </dd>
                </dl>

                @if ($application->candidateProfile?->summary)
                    <hr>
                    <h5>Candidate Summary</h5>
                    <div style="white-space: pre-line">{{ $application->candidateProfile->summary }}</div>
                @endif

                @if ($application->candidateProfile?->skills->isNotEmpty())
                    <hr>
                    <h5>Skills</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($application->candidateProfile->skills as $skill)
                            <span class="badge text-bg-light">
                                {{ $skill->name }}{{ $skill->proficiency ? ' - ' . ucfirst($skill->proficiency) : '' }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if ($application->candidateProfile?->workExperiences->isNotEmpty())
                    <hr>
                    <h5>Experience</h5>
                    @foreach ($application->candidateProfile->workExperiences as $experience)
                        <div class="mb-3">
                            <strong>{{ $experience->job_title }}</strong>
                            <div>{{ $experience->company_name }}</div>
                            <div class="text-muted small">
                                {{ optional($experience->start_date)->format('M Y') ?: '-' }}
                                -
                                {{ $experience->is_current ? 'Present' : (optional($experience->end_date)->format('M Y') ?: '-') }}
                            </div>
                            @if ($experience->description)
                                <div class="mt-1" style="white-space: pre-line">{{ $experience->description }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif

                @if ($application->candidateProfile?->educations->isNotEmpty())
                    <hr>
                    <h5>Education</h5>
                    @foreach ($application->candidateProfile->educations as $education)
                        <div class="mb-2">
                            <strong>{{ $education->school_name }}</strong>
                            <div>{{ trim(($education->degree ?: '') . ' ' . ($education->field_of_study ?: '')) ?: '-' }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Application</strong>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Job</dt>
                    <dd class="col-sm-8">
                        @if ($application->jobPost)
                            <a href="{{ route('admin.jobs.show', $application->jobPost) }}">{{ $application->jobPost->title }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">
                        @if ($application->company)
                            <a href="{{ route('admin.companies.show', $application->company) }}">{{ $application->company->company_name }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Source</dt>
                    <dd class="col-sm-8">{{ $sources[$application->source] ?? ucfirst($application->source) }}</dd>

                    <dt class="col-sm-4">Current Stage</dt>
                    <dd class="col-sm-8">{{ $application->current_stage ?: '-' }}</dd>

                    <dt class="col-sm-4">Applied At</dt>
                    <dd class="col-sm-8">{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</dd>

                    <dt class="col-sm-4">Last Status Change</dt>
                    <dd class="col-sm-8">{{ $application->last_status_changed_at ? $application->last_status_changed_at->format('d M Y H:i') : '-' }}</dd>

                    <dt class="col-sm-4">Reviewed By</dt>
                    <dd class="col-sm-8">{{ $application->reviewedBy?->name ?: '-' }}</dd>
                </dl>

                @if ($application->cover_letter)
                    <hr>
                    <h5>Cover Letter</h5>
                    <div style="white-space: pre-line">{{ $application->cover_letter }}</div>
                @endif

                @if ($application->admin_notes)
                    <hr>
                    <h5>Admin Notes</h5>
                    <div style="white-space: pre-line">{{ $application->admin_notes }}</div>
                @endif
            </div>
        </div>

        <div class="card table-card mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Interview Schedule</strong>
                <span class="text-muted small">{{ $application->interviews->count() }} schedule(s)</span>
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
                                @if ($interview->google_sync_status && $interview->google_sync_status !== 'manual')
                                    <span class="badge text-bg-{{ $interview->google_sync_status === 'synced' ? 'success' : ($interview->google_sync_status === 'failed' ? 'danger' : 'warning') }}">
                                        Google {{ $interview->googleSyncLabel() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <i class="mdi mdi-calendar-clock me-1"></i>
                            {{ $interview->scheduledAtLabel() }}
                        </div>
                        <div class="text-muted small">
                            {{ $interview->typeLabel() }} - {{ $interview->durationLabel() }}
                            @if ($interview->scheduledBy)
                                - by {{ $interview->scheduledBy->name }}
                            @endif
                        </div>

                        @php($meetingUrl = $interview->meetingUrl())
                        @if ($meetingUrl)
                            <div class="mt-2">
                                <a href="{{ $meetingUrl }}" target="_blank" rel="noopener">
                                    {{ $meetingUrl }}
                                </a>
                            </div>
                        @endif

                        @if ($interview->google_calendar_event_url)
                            <div class="mt-2 small">
                                <a href="{{ $interview->google_calendar_event_url }}" target="_blank" rel="noopener">
                                    Open Google Calendar event
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

        <div class="card table-card mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Message Thread</strong>
                <span class="text-muted small">{{ $application->messages->count() }} message(s)</span>
            </div>

            <div class="card-body">
                @forelse ($application->messages as $message)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <strong>{{ $message->sender?->name ?: $message->senderLabel() }}</strong>
                            <span class="text-muted small">{{ $message->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="text-muted small">{{ $message->senderLabel() }}</div>
                        <div class="mt-2" style="white-space: pre-line">{{ $message->body }}</div>
                    </div>
                @empty
                    <div class="text-muted">Belum ada percakapan antara kandidat dan recruiter.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card table-card mb-4">
            <div class="card-header bg-white">
                <strong>Update Status</strong>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.applications.update', $application) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $application->status) === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Stage</label>
                        <input type="text" name="current_stage" class="form-control" value="{{ old('current_stage', $application->current_stage) }}" placeholder="Screening, HR interview, user interview...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Note</label>
                        <textarea name="status_note" rows="3" class="form-control" placeholder="Catatan untuk perubahan status ini">{{ old('status_note') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" rows="5" class="form-control">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                    </div>

                    <button class="btn btn-primary w-100">Update Application</button>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Status History</strong>
            </div>

            <div class="card-body">
                @forelse ($application->statusHistories as $history)
                    <div class="mb-3 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-secondary">{{ $history->from_status ?: 'new' }}</span>
                            <span class="text-muted">to</span>
                            <span class="badge bg-{{ $statusColors[$history->to_status] ?? 'primary' }}">{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $history->changed_at->format('d M Y H:i') }} by {{ $history->changedBy?->name ?: 'System' }}
                        </div>
                        @if ($history->notes)
                            <div class="mt-2" style="white-space: pre-line">{{ $history->notes }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted">Belum ada histori status.</div>
                @endforelse
            </div>
        </div>

        <a href="{{ route('admin.applications.index') }}" class="btn btn-link mt-3">&larr; Back to Applications</a>
    </div>
</div>
@endsection
