<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\JobReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobReportController extends Controller
{
    public function store(Request $request, JobPost $jobPost)
    {
        $data = $request->validate(['reason' => ['required', Rule::in(JobReport::REASONS)], 'details' => ['nullable', 'string', 'max:2000'], 'reporter_email' => ['nullable', 'email', 'max:255']]);
        $data['ip_hash'] = hash('sha256', ($request->ip() ?: 'unknown').'|'.config('app.key'));
        $jobPost->reports()->create($data);
        $jobPost->increment('report_count');

        return back()->with('success', 'Laporan diterima dan akan ditinjau. Terima kasih telah menjaga kualitas lowongan.');
    }
}
