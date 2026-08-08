<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->where('status', 'active')
            ->whereHas('jobPosts', fn ($jobs) => $jobs->publiclyVisible())
            ->withCount(['jobPosts as open_jobs_count' => fn ($jobs) => $jobs->publiclyVisible()])
            ->orderBy('company_name')
            ->paginate(24);

        return view('frontend.companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        abort_if($company->status !== 'active', 404);

        $jobs = $company->jobPosts()->publiclyVisible()->latest('published_at')->paginate(12);
        abort_if($jobs->total() === 0, 404);

        return view('frontend.companies.show', compact('company', 'jobs'));
    }
}
