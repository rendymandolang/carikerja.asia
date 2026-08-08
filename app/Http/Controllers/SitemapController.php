<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            $this->url(route('landing'), 'daily', '1.0'),
        ]);

        $jobs = JobPost::query()
            ->publiclyVisible()
            ->latest('published_at')
            ->limit(45000)
            ->get();

        Company::query()
            ->where('status', 'active')
            ->whereHas('jobPosts', fn ($query) => $query->publiclyVisible())
            ->each(fn (Company $company) => $urls->push($this->url(
                route('companies.show', $company),
                'weekly',
                '0.7',
                $company->updated_at?->toAtomString(),
            )));

        $jobs->pluck('city')->filter()->unique()->each(fn (string $city) => $urls->push(
            $this->url(route('jobs.city', Str::slug($city)), 'daily', '0.7'),
        ));

        $jobs->pluck('employment_type')->filter()->unique()->each(fn (string $type) => $urls->push(
            $this->url(route('jobs.category', $type), 'daily', '0.7'),
        ));

        foreach ($jobs as $job) {
            $urls->push($this->url(
                route('jobs.show', $job),
                'daily',
                '0.8',
                $job->updated_at?->toAtomString(),
            ));
        }

        $xml = view('sitemap', ['urls' => $urls->unique('loc')->values()])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function url(string $loc, string $changefreq, string $priority, ?string $lastmod = null): array
    {
        return compact('loc', 'changefreq', 'priority', 'lastmod');
    }
}
