<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('candidate.login');
        }

        if ($request->user()->role !== 'candidate') {
            abort(403, 'Unauthorized candidate access.');
        }

        if ($request->user()->account_status !== 'active') {
            abort(403, 'Candidate account is not active.');
        }

        return $next($request);
    }
}
