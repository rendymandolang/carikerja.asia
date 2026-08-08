<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Company Name <span class="text-danger">*</span></label>
        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $company->company_name) }}" required>
        @error('company_name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Legal Name</label>
        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $company->legal_name) }}">
        @error('legal_name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Industry</label>
        <input type="text" name="industry" class="form-control" value="{{ old('industry', $company->industry) }}" placeholder="Hospitality, Retail, Technology...">
        @error('industry') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Website</label>
        <input type="text" name="website" class="form-control" value="{{ old('website', $company->website) }}" placeholder="https://company.com">
        @error('website') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Company Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
        @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $company->city) }}">
        @error('city') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" value="{{ old('province', $company->province) }}">
        @error('province') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Address</label>
        <textarea name="address" rows="3" class="form-control">{{ old('address', $company->address) }}</textarea>
        @error('address') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="pending" @selected(old('status', $company->status) === 'pending')>Pending</option>
            <option value="active" @selected(old('status', $company->status) === 'active')>Active</option>
            <option value="suspended" @selected(old('status', $company->status) === 'suspended')>Suspended</option>
            <option value="rejected" @selected(old('status', $company->status) === 'rejected')>Rejected</option>
        </select>
        @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Admin Notes</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $company->notes) }}</textarea>
        @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input type="hidden" name="is_verified" value="0">
            <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="is_verified" @checked(old('is_verified', $company->is_verified))>
            <label class="form-check-label" for="is_verified"><strong>Perusahaan terverifikasi</strong> — aktifkan hanya setelah legalitas dan identitas recruiter diperiksa.</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
