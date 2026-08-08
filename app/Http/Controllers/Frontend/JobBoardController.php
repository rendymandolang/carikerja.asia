<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobBoardController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPost::query()
            ->with('company')
            ->publiclyVisible()
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
            ->publiclyVisible()
            ->whereNotNull('city')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('frontend.jobs.index', [
            'jobs' => $jobs,
            'cities' => $cities,
            'seoTitle' => 'Lowongan Kerja - carikerja.asia',
            'seoDescription' => 'Temukan lowongan kerja aktif dari perusahaan nyata dengan proses rekrutmen yang lebih transparan di carikerja.asia.',
            'canonicalUrl' => route('jobs.index'),
            'heading' => 'Cari kerja tanpa digantung.',
            'intro' => 'Temukan lowongan yang statusnya jelas, prosesnya lebih transparan, dan perusahaan lebih accountable.',
        ]);
    }

    public function city(string $citySlug)
    {
        $city = $this->publicCities()->first(fn (string $name) => Str::slug($name) === $citySlug);
        abort_unless($city, 404);

        return $this->curatedListing(
            JobPost::query()->where('city', $city),
            "Lowongan Kerja di {$city}",
            "Temukan lowongan kerja aktif di {$city} dengan proses rekrutmen yang transparan dan status lamaran yang jelas.",
            route('jobs.city', $citySlug),
        );
    }

    public function category(string $employmentType)
    {
        $categories = $this->categories();
        abort_unless(array_key_exists($employmentType, $categories), 404);

        return $this->curatedListing(
            JobPost::query()->where('employment_type', $employmentType),
            'Lowongan '.$categories[$employmentType],
            'Temukan lowongan '.strtolower($categories[$employmentType]).' aktif dengan proses rekrutmen yang transparan di carikerja.asia.',
            route('jobs.category', $employmentType),
        );
    }

    public function show(JobPost $jobPost)
    {
        $jobPost->load('company');
        abort_if(! $jobPost->isPublished() || ! $jobPost->isOpenForApplication() || $jobPost->company?->status !== 'active', 410);

        $job = $jobPost;

        return view('frontend.jobs.show', compact('job'));
    }

    private function curatedListing($query, string $title, string $description, string $canonical)
    {
        $jobs = $query->with('company')->publiclyVisible()->latest('published_at')->paginate(12);

        return view('frontend.jobs.index', [
            'jobs' => $jobs,
            'cities' => $this->publicCities(),
            'seoTitle' => $title.' - carikerja.asia',
            'seoDescription' => $description,
            'canonicalUrl' => $canonical,
            'heading' => $title,
            'intro' => $description,
        ]);
    }

    private function publicCities()
    {
        return JobPost::query()
            ->publiclyVisible()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    private function categories(): array
    {
        return [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Kontrak',
            'internship' => 'Magang',
            'freelance' => 'Freelance',
        ];
    }
}
