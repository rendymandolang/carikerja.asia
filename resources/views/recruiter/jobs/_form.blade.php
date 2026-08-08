<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Company <span class="text-danger">*</span></label>
        <select name="company_id" class="form-select" required>
            <option value="">Select Company</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected((string) old('company_id', $job->company_id) === (string) $company->id)>
                    {{ $company->company_name }}
                </option>
            @endforeach
        </select>
        @error('company_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Job Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $job->title) }}" required>
        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="{{ old('department', $job->department) }}" placeholder="Rooms, F&B, Sales, HR...">
        @error('department') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Location Detail</label>
        <input type="text" name="location" class="form-control" value="{{ old('location', $job->location) }}" placeholder="Property, branch, office...">
        @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $job->city) }}">
        @error('city') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" value="{{ old('province', $job->province) }}">
        @error('province') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Country</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $job->country ?: 'Indonesia') }}" required>
        @error('country') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Employment Type</label>
        <select name="employment_type" class="form-select" required>
            <option value="full_time" @selected(old('employment_type', $job->employment_type) === 'full_time')>Full Time</option>
            <option value="part_time" @selected(old('employment_type', $job->employment_type) === 'part_time')>Part Time</option>
            <option value="contract" @selected(old('employment_type', $job->employment_type) === 'contract')>Contract</option>
            <option value="internship" @selected(old('employment_type', $job->employment_type) === 'internship')>Internship</option>
            <option value="freelance" @selected(old('employment_type', $job->employment_type) === 'freelance')>Freelance</option>
        </select>
        @error('employment_type') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Work Arrangement</label>
        <select name="work_arrangement" class="form-select" required>
            <option value="onsite" @selected(old('work_arrangement', $job->work_arrangement) === 'onsite')>Onsite</option>
            <option value="hybrid" @selected(old('work_arrangement', $job->work_arrangement) === 'hybrid')>Hybrid</option>
            <option value="remote" @selected(old('work_arrangement', $job->work_arrangement) === 'remote')>Remote</option>
        </select>
        @error('work_arrangement') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="draft" @selected(old('status', $job->status) === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $job->status) === 'published')>Published</option>
            <option value="closed" @selected(old('status', $job->status) === 'closed')>Closed</option>
            <option value="archived" @selected(old('status', $job->status) === 'archived')>Archived</option>
        </select>
        @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="{{ old('currency', $job->currency ?: 'IDR') }}" required>
        @error('currency') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Salary Min</label>
        <input type="number" step="0.01" name="salary_min" class="form-control" value="{{ old('salary_min', $job->salary_min) }}">
        @error('salary_min') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Salary Max</label>
        <input type="number" step="0.01" name="salary_max" class="form-control" value="{{ old('salary_max', $job->salary_max) }}">
        @error('salary_max') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Application Deadline</label>
        <input type="date" name="application_deadline" class="form-control" value="{{ old('application_deadline', optional($job->application_deadline)->format('Y-m-d')) }}">
        @error('application_deadline') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Alasan Penutupan</label>
        <select name="closure_type" class="form-select">
            <option value="">Pilih jika status Closed</option>
            <option value="filled" @selected(old('closure_type', $job->closure_type) === 'filled')>Posisi sudah terisi</option>
            <option value="cancelled" @selected(old('closure_type', $job->closure_type) === 'cancelled')>Posisi dibatalkan</option>
            <option value="other" @selected(old('closure_type', $job->closure_type) === 'other')>Alasan lain</option>
        </select>
        @error('closure_type') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8">
        <label class="form-label">Penjelasan Penutupan</label>
        <input name="closed_reason" class="form-control" value="{{ old('closed_reason', $job->closed_reason) }}" placeholder="Wajib jika status Closed; kandidat akan menerima alasan ini.">
        @error('closed_reason') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Job Description <span class="text-danger">*</span></label>
        <textarea name="description" rows="7" class="form-control" required>{{ old('description', $job->description) }}</textarea>
        @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Requirements</label>
        <textarea name="requirements" rows="6" class="form-control">{{ old('requirements', $job->requirements) }}</textarea>
        @error('requirements') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Benefits</label>
        <textarea name="benefits" rows="5" class="form-control">{{ old('benefits', $job->benefits) }}</textarea>
        @error('benefits') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('recruiter.jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
