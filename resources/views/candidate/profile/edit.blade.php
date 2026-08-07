@extends('candidate.layouts.app')

@section('title', 'Resume Center')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Resume Center</h1>
        <div class="text-muted">Format profil profesional yang kompatibel untuk matching ala LinkedIn dan Indeed.</div>
    </div>
    <a href="{{ route('candidate.job-matches.index') }}" class="btn btn-primary">View Matches</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card portal-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Profile</strong>
                <span class="badge bg-primary">{{ $completion }}% complete</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('candidate.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $candidate->full_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $candidate->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Headline</label>
                            <input type="text" name="headline" class="form-control" value="{{ old('headline', $candidate->headline) }}" placeholder="Frontend Engineer, Hotel GM, Sales Manager...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position" class="form-control" value="{{ old('current_position', $candidate->current_position) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Desired Job Title</label>
                            <input type="text" name="desired_job_title" class="form-control" value="{{ old('desired_job_title', $candidate->desired_job_title) }}" placeholder="Role yang dicari">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Availability</label>
                            <select name="availability_status" class="form-select" required>
                                <option value="immediate" @selected(old('availability_status', $candidate->availability_status) === 'immediate')>Immediate</option>
                                <option value="notice_period" @selected(old('availability_status', $candidate->availability_status) === 'notice_period')>Notice period</option>
                                <option value="open_to_offers" @selected(old('availability_status', $candidate->availability_status) === 'open_to_offers')>Open to offers</option>
                                <option value="not_looking" @selected(old('availability_status', $candidate->availability_status) === 'not_looking')>Not looking</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $candidate->location) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $candidate->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" class="form-control" value="{{ old('province', $candidate->province) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $candidate->country ?: 'Indonesia') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Desired Employment</label>
                            <select name="desired_employment_type" class="form-select">
                                <option value="">Any</option>
                                <option value="full_time" @selected(old('desired_employment_type', $candidate->desired_employment_type) === 'full_time')>Full Time</option>
                                <option value="part_time" @selected(old('desired_employment_type', $candidate->desired_employment_type) === 'part_time')>Part Time</option>
                                <option value="contract" @selected(old('desired_employment_type', $candidate->desired_employment_type) === 'contract')>Contract</option>
                                <option value="internship" @selected(old('desired_employment_type', $candidate->desired_employment_type) === 'internship')>Internship</option>
                                <option value="freelance" @selected(old('desired_employment_type', $candidate->desired_employment_type) === 'freelance')>Freelance</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Desired Work Arrangement</label>
                            <select name="desired_work_arrangement" class="form-select">
                                <option value="">Any</option>
                                <option value="onsite" @selected(old('desired_work_arrangement', $candidate->desired_work_arrangement) === 'onsite')>Onsite</option>
                                <option value="hybrid" @selected(old('desired_work_arrangement', $candidate->desired_work_arrangement) === 'hybrid')>Hybrid</option>
                                <option value="remote" @selected(old('desired_work_arrangement', $candidate->desired_work_arrangement) === 'remote')>Remote</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $candidate->currency ?: 'IDR') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected Salary Min</label>
                            <input type="number" step="0.01" name="expected_salary_min" class="form-control" value="{{ old('expected_salary_min', $candidate->expected_salary_min) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected Salary Max</label>
                            <input type="number" step="0.01" name="expected_salary_max" class="form-control" value="{{ old('expected_salary_max', $candidate->expected_salary_max) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $candidate->linkedin_url) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Indeed URL</label>
                            <input type="url" name="indeed_url" class="form-control" value="{{ old('indeed_url', $candidate->indeed_url) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $candidate->portfolio_url) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Professional Summary</label>
                            <textarea name="summary" rows="5" class="form-control">{{ old('summary', $candidate->summary) }}</textarea>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-4">Save Profile</button>
                </form>
            </div>
        </div>

        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Work Experience</strong>
            </div>
            <div class="card-body">
                @foreach ($candidate->workExperiences as $experience)
                    <div class="border rounded p-3 mb-3">
                        <form id="experience-update-{{ $experience->id }}" method="POST" action="{{ route('candidate.profile.experiences.update', $experience) }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-2">
                                <div class="col-md-6"><input name="job_title" class="form-control" value="{{ $experience->job_title }}" required></div>
                                <div class="col-md-6"><input name="company_name" class="form-control" value="{{ $experience->company_name }}" required></div>
                                <div class="col-md-4"><input name="employment_type" class="form-control" value="{{ $experience->employment_type }}" placeholder="Full-time, Contract..."></div>
                                <div class="col-md-4"><input name="location" class="form-control" value="{{ $experience->location }}" placeholder="Location"></div>
                                <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="{{ optional($experience->start_date)->format('Y-m-d') }}"></div>
                                <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="{{ optional($experience->end_date)->format('Y-m-d') }}"></div>
                                <div class="col-12"><textarea name="description" rows="3" class="form-control">{{ $experience->description }}</textarea></div>
                            </div>
                        </form>
                        <div class="d-flex gap-2 mt-3">
                            <button form="experience-update-{{ $experience->id }}" class="btn btn-sm btn-outline-primary">Update</button>
                            <form method="POST" action="{{ route('candidate.profile.experiences.delete', $experience) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('candidate.profile.experiences.store') }}" class="border rounded p-3 bg-light">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6"><input name="job_title" class="form-control" placeholder="Job title" required></div>
                        <div class="col-md-6"><input name="company_name" class="form-control" placeholder="Company" required></div>
                        <div class="col-md-4"><input name="employment_type" class="form-control" placeholder="Employment type"></div>
                        <div class="col-md-4"><input name="location" class="form-control" placeholder="Location"></div>
                        <div class="col-md-2"><input type="date" name="start_date" class="form-control"></div>
                        <div class="col-md-2"><input type="date" name="end_date" class="form-control"></div>
                        <div class="col-12"><textarea name="description" rows="3" class="form-control" placeholder="Achievements, responsibilities, metrics..."></textarea></div>
                    </div>
                    <button class="btn btn-sm btn-primary mt-3">Add Experience</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Resume File</strong>
            </div>
            <div class="card-body">
                @if ($candidate->resume_path)
                    <a href="{{ route('candidate.profile.resume.download') }}" class="btn btn-outline-primary w-100 mb-3">Download Current Resume</a>
                @else
                    <div class="text-muted mb-3">Belum ada file resume.</div>
                @endif
                <form method="POST" action="{{ route('candidate.profile.resume.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="resume" class="form-control mb-3" accept=".pdf,.doc,.docx" required>
                    <button class="btn btn-primary w-100">Upload Resume</button>
                </form>
            </div>
        </div>

        <div class="card portal-card mb-4">
            <div class="card-header bg-white">
                <strong>Skills</strong>
            </div>
            <div class="card-body">
                @foreach ($candidate->skills as $skill)
                    <div class="border rounded p-2 mb-2">
                        <form id="skill-update-{{ $skill->id }}" method="POST" action="{{ route('candidate.profile.skills.update', $skill) }}">
                            @csrf
                            @method('PUT')
                            <input name="name" class="form-control form-control-sm mb-2" value="{{ $skill->name }}" required>
                            <select name="proficiency" class="form-select form-select-sm mb-2">
                                <option value="">Proficiency</option>
                                <option value="beginner" @selected($skill->proficiency === 'beginner')>Beginner</option>
                                <option value="intermediate" @selected($skill->proficiency === 'intermediate')>Intermediate</option>
                                <option value="advanced" @selected($skill->proficiency === 'advanced')>Advanced</option>
                                <option value="expert" @selected($skill->proficiency === 'expert')>Expert</option>
                            </select>
                        </form>
                        <div class="d-flex gap-2">
                            <button form="skill-update-{{ $skill->id }}" class="btn btn-sm btn-outline-primary">Update</button>
                            <form method="POST" action="{{ route('candidate.profile.skills.delete', $skill) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('candidate.profile.skills.store') }}" class="bg-light rounded p-2">
                    @csrf
                    <input name="name" class="form-control form-control-sm mb-2" placeholder="Skill name" required>
                    <select name="proficiency" class="form-select form-select-sm mb-2">
                        <option value="">Proficiency</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="expert">Expert</option>
                    </select>
                    <button class="btn btn-sm btn-primary">Add Skill</button>
                </form>
            </div>
        </div>

        <div class="card portal-card">
            <div class="card-header bg-white">
                <strong>Education</strong>
            </div>
            <div class="card-body">
                @foreach ($candidate->educations as $education)
                    <div class="border rounded p-2 mb-2">
                        <form id="education-update-{{ $education->id }}" method="POST" action="{{ route('candidate.profile.educations.update', $education) }}">
                            @csrf
                            @method('PUT')
                            <input name="school_name" class="form-control form-control-sm mb-2" value="{{ $education->school_name }}" required>
                            <input name="degree" class="form-control form-control-sm mb-2" value="{{ $education->degree }}" placeholder="Degree">
                            <input name="field_of_study" class="form-control form-control-sm mb-2" value="{{ $education->field_of_study }}" placeholder="Field of study">
                        </form>
                        <div class="d-flex gap-2">
                            <button form="education-update-{{ $education->id }}" class="btn btn-sm btn-outline-primary">Update</button>
                            <form method="POST" action="{{ route('candidate.profile.educations.delete', $education) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('candidate.profile.educations.store') }}" class="bg-light rounded p-2">
                    @csrf
                    <input name="school_name" class="form-control form-control-sm mb-2" placeholder="School" required>
                    <input name="degree" class="form-control form-control-sm mb-2" placeholder="Degree">
                    <input name="field_of_study" class="form-control form-control-sm mb-2" placeholder="Field of study">
                    <button class="btn btn-sm btn-primary">Add Education</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
