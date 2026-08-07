<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            $this->url(route('landing'), 'daily', '1.0'),
            $this->url(route('jobs.index'), 'hourly', '0.9'),
            $this->url(route('candidate.login'), 'monthly', '0.5'),
            $this->url(route('recruiter.login'), 'monthly', '0.5'),
            $this->url(route('legal.privacy'), 'yearly', '0.4'),
            $this->url(route('legal.terms'), 'yearly', '0.4'),
            $this->url(route('legal.cookies'), 'yearly', '0.4'),
        ]);

        $jobs = JobPost::query()
            ->published()
            ->openForApplication()
            ->latest('published_at')
            ->limit(1000)
            ->get();

        foreach ($jobs as $job) {
            $urls->push($this->url(
                route('jobs.show', $job),
                'daily',
                '0.8',
                $job->updated_at?->toAtomString(),
            ));
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function url(string $loc, string $changefreq, string $priority, ?string $lastmod = null): array
    {
        return compact('loc', 'changefreq', 'priority', 'lastmod');
    }
}
