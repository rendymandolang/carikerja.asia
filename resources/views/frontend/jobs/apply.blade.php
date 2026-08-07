@extends('frontend.layouts.app')

@section('title', 'Apply ' . $job->title . ' - carikerja.asia')
@section('meta_description', 'Kirim lamaran untuk ' . $job->title . ' melalui carikerja.asia.')

@section('content')
<section class="hero py-5 mb-5">
    <div class="container py-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <span class="pill mb-3">Apply Now</span>
                <h1 class="display-5 fw-bold mb-3">{{ $job->title }}</h1>
                <p class="lead text-white-50 mb-0">
                    {{ $job->company?->company_name ?: 'Company confidential' }}
                </p>
            </div>

            <div class="col-lg-4">
                <div class="bg-white text-dark rounded-4 p-4 shadow">
                    <div class="text-muted small">Deadline</div>
                    <div class="fw-bold fs-5">
                        {{ $job->application_deadline ? $job->application_deadline->format('d M Y') : 'Open until filled' }}
                    </div>
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-primary w-100 mt-3">
                        View Job Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('jobs.apply.store', $job) }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card content-card mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Candidate Profile</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', auth()->user()?->role === 'candidate' ? auth()->user()->name : '') }}" required>
                                @error('full_name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()?->role === 'candidate' ? auth()->user()->email : '') }}" required>
                                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Current Position</label>
                                <input type="text" name="current_position" class="form-control" value="{{ old('current_position') }}">
                                @error('current_position') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Headline</label>
                                <input type="text" name="headline" class="form-control" value="{{ old('headline') }}" placeholder="Product Designer, Backend Engineer, HR Generalist...">
                                @error('headline') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="text" name="linkedin_url" class="form-control" value="{{ old('linkedin_url') }}">
                                @error('linkedin_url') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Portfolio URL</label>
                                <input type="text" name="portfolio_url" class="form-control" value="{{ old('portfolio_url') }}">
                                @error('portfolio_url') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Jakarta, Bandung, Remote...">
                                @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                @error('city') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Province</label>
                                <input type="text" name="province" class="form-control" value="{{ old('province') }}">
                                @error('province') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card content-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Application Details</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Expected Salary Min</label>
                                <input type="number" step="0.01" name="expected_salary_min" class="form-control" value="{{ old('expected_salary_min') }}">
                                @error('expected_salary_min') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Expected Salary Max</label>
                                <input type="number" step="0.01" name="expected_salary_max" class="form-control" value="{{ old('expected_salary_max') }}">
                                @error('expected_salary_max') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Availability</label>
                                <select name="availability_status" class="form-select" required>
                                    <option value="open_to_offers" @selected(old('availability_status', 'open_to_offers') === 'open_to_offers')>Open to offers</option>
                                    <option value="immediate" @selected(old('availability_status') === 'immediate')>Immediate</option>
                                    <option value="notice_period" @selected(old('availability_status') === 'notice_period')>Notice period</option>
                                    <option value="not_looking" @selected(old('availability_status') === 'not_looking')>Not looking</option>
                                </select>
                                @error('availability_status') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Resume</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                                <div class="text-muted small mt-1">PDF, DOC, or DOCX. Max 4 MB.</div>
                                @error('resume') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Profile Summary</label>
                                <textarea name="summary" rows="5" class="form-control">{{ old('summary') }}</textarea>
                                @error('summary') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Cover Letter</label>
                                <textarea name="cover_letter" rows="6" class="form-control">{{ old('cover_letter') }}</textarea>
                                @error('cover_letter') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if (! auth()->check() || auth()->user()->role !== 'candidate')
                    <div class="card content-card mt-4">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Candidate Account</h4>
                            <p class="text-muted">
                                Buat password agar Anda bisa login dan memantau status lamaran dari Candidate Portal.
                                Jika email sudah terdaftar, gunakan password akun kandidat Anda.
                            </p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                    @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('candidate.login') }}">Sudah punya akun kandidat? Login di sini.</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card content-card mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Job Summary</h5>

                        <div class="mb-3">
                            <div class="text-muted small">Company</div>
                            <div class="fw-bold">{{ $job->company?->company_name ?: '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Location</div>
                            <div class="fw-bold">
                                {{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: '-') }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Employment Type</div>
                            <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Work Arrangement</div>
                            <div class="fw-bold">{{ ucfirst($job->work_arrangement) }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Salary</div>
                            <div class="fw-bold">{{ $job->salaryRangeLabel() }}</div>
                        </div>

                        <button class="btn btn-primary w-100">Submit Application</button>
                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-secondary w-100 mt-2">Back to Job</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>
@endsection
