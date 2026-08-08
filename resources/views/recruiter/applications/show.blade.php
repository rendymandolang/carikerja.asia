@extends('recruiter.layouts.app')

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
                <strong>{{ $application->candidateProfile?->full_name ?: 'Candidate' }}</strong>
                <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }} fs-6">
                    {{ $application->statusLabel() }}
                </span>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Email</dt>
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
                            <a href="{{ route('recruiter.applications.resume', $application) }}" class="btn btn-sm btn-outline-primary">
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

        <div class="card portal-card">
            <div class="card-header bg-white">
                <strong>Application</strong>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Job</dt>
                    <dd class="col-sm-8">
                        @if ($application->jobPost)
                            <a href="{{ route('recruiter.jobs.show', $application->jobPost) }}">{{ $application->jobPost->title }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">{{ $application->company?->company_name ?: '-' }}</dd>

                    <dt class="col-sm-4">Current Stage</dt>
                    <dd class="col-sm-8">{{ $application->current_stage ?: '-' }}</dd>

                    <dt class="col-sm-4">Applied At</dt>
                    <dd class="col-sm-8">{{ $application->applied_at ? $application->applied_at->format('d M Y H:i') : '-' }}</dd>

                    <dt class="col-sm-4">Last Status Change</dt>
                    <dd class="col-sm-8">{{ $application->last_status_changed_at ? $application->last_status_changed_at->format('d M Y H:i') : '-' }}</dd>

                    <dt class="col-sm-4">Batas Respons Pertama</dt>
                    <dd class="col-sm-8">
                        @if ($application->first_responded_at)
                            <span class="badge bg-success">Direspons {{ $application->first_responded_at->format('d M Y H:i') }}</span>
                        @elseif ($application->isResponseOverdue())
                            <span class="badge bg-danger">Terlambat sejak {{ $application->response_due_at->format('d M Y H:i') }}</span>
                        @else
                            {{ $application->response_due_at?->format('d M Y H:i') ?: '-' }}
                        @endif
                    </dd>

                    @if ($application->isFinalized())
                        <dt class="col-sm-4">Hasil Akhir</dt>
                        <dd class="col-sm-8"><strong>{{ $application->resolutionLabel() }}</strong><div class="text-muted">{{ $application->final_reason }}</div></dd>
                    @endif

                    <dt class="col-sm-4">Reviewed By</dt>
                    <dd class="col-sm-8">{{ $application->reviewedBy?->name ?: '-' }}</dd>
                </dl>

                @if ($application->cover_letter)
                    <hr>
                    <h5>Cover Letter</h5>
                    <div style="white-space: pre-line">{{ $application->cover_letter }}</div>
                @endif
            </div>
        </div>

        <div class="card portal-card mb-4">
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

                        @if ($interview->google_sync_status === 'failed' && $interview->google_sync_error)
                            <div class="text-danger small mt-2">{{ $interview->google_sync_error }}</div>
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
                        @php($isMine = $message->isFromRecruiter())
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
                        <div class="text-muted">Belum ada pesan. Mulai komunikasi dengan kandidat langsung dari application ini.</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('recruiter.applications.messages.store', $application) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Message Candidate</label>
                        <textarea name="body" rows="4" class="form-control" maxlength="3000" required placeholder="Tulis pesan untuk kandidat...">{{ old('body') }}</textarea>
                        @error('body') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Schedule Interview</strong>
            </div>

            <div class="card-body">
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    @if ($googleWorkspace?->isConnected())
                        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                            <div>
                                <div class="fw-semibold">
                                    <i class="mdi mdi-google me-1"></i>
                                    Google Workspace connected
                                </div>
                                <div class="text-muted small">{{ $googleWorkspace->google_email }}</div>
                            </div>
                            <form method="POST" action="{{ route('recruiter.google-workspace.disconnect') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                            </form>
                        </div>
                    @else
                        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                            <div>
                                <div class="fw-semibold">Google Calendar & Meet</div>
                                <div class="text-muted small">Connect Google Workspace untuk membuat event dan Meet link otomatis.</div>
                            </div>
                            <a href="{{ route('recruiter.google-workspace.redirect') }}" class="btn btn-sm btn-outline-primary">
                                Connect Google
                            </a>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('recruiter.applications.interviews.store', $application) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', 'Interview - ' . ($application->jobPost?->title ?: 'Application')) }}">
                        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="interview_type" class="form-select" required>
                            @foreach (\App\Models\ApplicationInterview::TYPES as $type)
                                <option value="{{ $type }}" @selected(old('interview_type', 'video') === $type)>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('interview_type') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}" required>
                        @error('scheduled_at') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Timezone</label>
                        <select name="timezone" class="form-select" required>
                            @foreach (\App\Models\ApplicationInterview::TIMEZONES as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', 'Asia/Jakarta') === $timezone)>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                        @error('timezone') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 60) }}" min="15" max="480" step="15" required>
                        @error('duration_minutes') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meeting Link</label>
                        <input type="url" name="meeting_url" class="form-control" value="{{ old('meeting_url') }}" placeholder="https://meet.google.com/...">
                        @error('meeting_url') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    @if ($googleWorkspace?->isConnected())
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="create_google_meet" value="1" id="create_google_meet" @checked(old('create_google_meet', true))>
                            <label class="form-check-label" for="create_google_meet">
                                Create Google Calendar event + Meet link
                            </label>
                            @error('create_google_meet') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Office address / room / phone number">
                        @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Agenda, preparation, interviewer...">{{ old('notes') }}</textarea>
                        @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary w-100">Schedule Interview</button>
                </form>
            </div>
        </div>

        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Update Status</strong>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('recruiter.applications.update', $application) }}">
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
                        <textarea name="status_note" rows="4" class="form-control" placeholder="Catatan untuk perubahan status ini">{{ old('status_note') }}</textarea>
                        <div class="form-text">Wajib diisi untuk diterima, ditolak, atau ditarik. Kandidat akan melihat alasan ini.</div>
                        @error('status_note') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary w-100">Update Application</button>
                </form>
            </div>
        </div>

        <div class="card portal-card">
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

        <a href="{{ route('recruiter.applications.index') }}" class="btn btn-link mt-3">&larr; Back to Applications</a>
    </div>
</div>
@endsection
