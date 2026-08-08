<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySeoIndexingPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) $request->route()?->getName();
        $noindex = $this->shouldNoindex($request, $routeName);

        if ($noindex) {
            $request->attributes->set('seo_robots', 'noindex,nofollow,noarchive');
        }

        $response = $next($request);

        if ($noindex) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }

    private function shouldNoindex(Request $request, string $routeName): bool
    {
        if ($routeName === 'jobs.index' && $request->query->count() > 0) {
            return true;
        }

        return $routeName === 'login'
            || $routeName === 'email.unsubscribe'
            || str_starts_with($routeName, 'admin.')
            || str_starts_with($routeName, 'candidate.')
            || str_starts_with($routeName, 'recruiter.')
            || str_starts_with($routeName, 'jobs.apply.');
    }
}
