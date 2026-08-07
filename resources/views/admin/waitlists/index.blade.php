@extends('admin.layouts.app')

@section('title', 'Waitlists')
@section('page_title', 'Waitlist Management')

@section('content')
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Nama, email, company..." value="{{ request('q') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="candidate" @selected(request('type') === 'candidate')>Candidate</option>
                    <option value="recruiter" @selected(request('type') === 'recruiter')>Recruiter</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="new" @selected(request('status') === 'new')>New</option>
                    <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                    <option value="qualified" @selected(request('status') === 'qualified')>Qualified</option>
                    <option value="onboarded" @selected(request('status') === 'onboarded')>Onboarded</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Waitlists</strong>
        <a href="{{ route('admin.waitlists.export', request()->query()) }}" class="btn btn-sm btn-success">
            Export CSV
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name / Company</th>
                    <th>Email</th>
                    <th>Role / Position</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($waitlists as $item)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $item->type === 'candidate' ? 'primary' : 'success' }}">
                                {{ ucfirst($item->type) }}
                            </span>
                        </td>
                        <td>{{ $item->full_name ?: $item->company_name }}</td>
                        <td>{{ $item->email ?: $item->company_email }}</td>
                        <td>{{ $item->target_role ?: $item->position }}</td>
                        <td>
                            <span class="badge bg-secondary badge-status">
                                {{ ucfirst($item->admin_status) }}
                            </span>
                        </td>
                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.waitlists.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $waitlists->links() }}
    </div>
</div>
@endsection
