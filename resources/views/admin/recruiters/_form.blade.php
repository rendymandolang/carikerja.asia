<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $recruiter->name) }}" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $recruiter->email) }}" required>
        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $recruiter->phone) }}">
        @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Password
            @if ($recruiter->exists)
                <span class="text-muted small">(kosongkan jika tidak diganti)</span>
            @else
                <span class="text-danger">*</span>
            @endif
        </label>
        <input type="password" name="password" class="form-control" {{ $recruiter->exists ? '' : 'required' }}>
        @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Company <span class="text-danger">*</span></label>
        <select name="company_id" class="form-select" required>
            <option value="">Select Company</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected((string) old('company_id', $selectedCompanyId) === (string) $company->id)>
                    {{ $company->company_name }} — {{ ucfirst($company->status) }}
                </option>
            @endforeach
        </select>
        @error('company_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Company Role <span class="text-danger">*</span></label>
        <select name="company_role" class="form-select" required>
            <option value="owner" @selected(old('company_role', $pivotData['company_role']) === 'owner')>Owner</option>
            <option value="admin" @selected(old('company_role', $pivotData['company_role']) === 'admin')>Company Admin</option>
            <option value="recruiter" @selected(old('company_role', $pivotData['company_role']) === 'recruiter')>Recruiter</option>
        </select>
        @error('company_role') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Job Title</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $pivotData['job_title']) }}" placeholder="HR Manager, Talent Acquisition...">
        @error('job_title') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Account Status</label>
        <select name="account_status" class="form-select" required>
            <option value="active" @selected(old('account_status', $recruiter->account_status) === 'active')>Active</option>
            <option value="inactive" @selected(old('account_status', $recruiter->account_status) === 'inactive')>Inactive</option>
            <option value="suspended" @selected(old('account_status', $recruiter->account_status) === 'suspended')>Suspended</option>
        </select>
        @error('account_status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Company Access</label>
        <select name="company_user_status" class="form-select" required>
            <option value="active" @selected(old('company_user_status', $pivotData['status']) === 'active')>Active</option>
            <option value="inactive" @selected(old('company_user_status', $pivotData['status']) === 'inactive')>Inactive</option>
            <option value="suspended" @selected(old('company_user_status', $pivotData['status']) === 'suspended')>Suspended</option>
        </select>
        @error('company_user_status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.recruiters.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
