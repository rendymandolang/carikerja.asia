@extends('admin.layouts.app')

@section('title', 'Waitlist Detail')
@section('page_title', 'Waitlist Detail')

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Profile Information</strong>
            </div>

            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Type</dt>
                    <dd class="col-sm-8">{{ ucfirst($waitlist->type) }}</dd>

                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $waitlist->full_name ?: $waitlist->contact_name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $waitlist->email ?: $waitlist->company_email }}</dd>

                    <dt class="col-sm-4">LinkedIn</dt>
                    <dd class="col-sm-8">
                        @if ($waitlist->linkedin_url)
                            <a href="{{ $waitlist->linkedin_url }}" target="_blank">{{ $waitlist->linkedin_url }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Target Role</dt>
                    <dd class="col-sm-8">{{ $waitlist->target_role ?: '-' }}</dd>

                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">{{ $waitlist->company_name ?: '-' }}</dd>

                    <dt class="col-sm-4">Position</dt>
                    <dd class="col-sm-8">{{ $waitlist->position ?: '-' }}</dd>

                    <dt class="col-sm-4">Notes</dt>
                    <dd class="col-sm-8">{{ $waitlist->notes ?: '-' }}</dd>

                    <dt class="col-sm-4">Registered</dt>
                    <dd class="col-sm-8">{{ $waitlist->created_at->format('d M Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white">
                <strong>Admin Follow-up</strong>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.waitlists.update', $waitlist) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="admin_status" class="form-select" required>
                            <option value="new" @selected($waitlist->admin_status === 'new')>New</option>
                            <option value="contacted" @selected($waitlist->admin_status === 'contacted')>Contacted</option>
                            <option value="qualified" @selected($waitlist->admin_status === 'qualified')>Qualified</option>
                            <option value="onboarded" @selected($waitlist->admin_status === 'onboarded')>Onboarded</option>
                            <option value="rejected" @selected($waitlist->admin_status === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" rows="6" class="form-control">{{ old('admin_notes', $waitlist->admin_notes) }}</textarea>
                    </div>

                    <button class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.waitlists.index') }}" class="btn btn-link mt-3">← Back to Waitlists</a>
    </div>
</div>
@endsection
