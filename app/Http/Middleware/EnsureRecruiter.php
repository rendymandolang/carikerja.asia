<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecruiter
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('recruiter.login');
        }

        if ($request->user()->role !== 'recruiter') {
            abort(403, 'Unauthorized recruiter access.');
        }

        if ($request->user()->account_status !== 'active') {
            abort(403, 'Recruiter account is not active.');
        }

        return $next($request);
    }
}
