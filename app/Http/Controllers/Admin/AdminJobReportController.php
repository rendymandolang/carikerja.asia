<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminJobReportController extends Controller
{
    public function update(Request $request, JobReport $jobReport)
    {
        $data = $request->validate(['status' => ['required', Rule::in(JobReport::STATUSES)], 'admin_notes' => ['nullable', 'string', 'max:2000']]);
        $jobReport->update($data + ['reviewed_at' => now(), 'reviewed_by_user_id' => Auth::id()]);

        return back()->with('success', 'Laporan lowongan telah diperbarui.');
    }
}
