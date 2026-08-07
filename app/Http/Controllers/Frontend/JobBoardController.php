<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;

class JobBoardController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPost::query()
            ->with('company')
            ->published()
            ->openForApplication()
            ->latest('published_at');

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('department', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('province', 'like', "%{$keyword}%")
                    ->orWhereHas('company', function ($companyQuery) use ($keyword) {
                        $companyQuery->where('company_name', 'like', "%{$keyword}%")
                            ->orWhere('industry', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('work_arrangement')) {
            $query->where('work_arrangement', $request->work_arrangement);
        }

        $jobs = $query->paginate(12)->withQueryString();

        $cities = JobPost::query()
            ->published()
            ->openForApplication()
            ->whereNotNull('city')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('frontend.jobs.index', [
            'jobs' => $jobs,
            'cities' => $cities,
        ]);
    }

    public function show(JobPost $jobPost)
    {
        abort_if(! $jobPost->isPublished(), 404);
        abort_if(! $jobPost->isOpenForApplication(), 404);

        $jobPost->load('company');

        $job = $jobPost;

        return view('frontend.jobs.show', compact('job'));
    }
}
