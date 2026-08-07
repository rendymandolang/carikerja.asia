<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Waitlist;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalWaitlists' => Waitlist::count(),
            'totalCandidates' => Waitlist::where('type', 'candidate')->count(),
            'totalRecruiters' => Waitlist::where('type', 'recruiter')->count(),
            'newWaitlists' => Waitlist::where('admin_status', 'new')->count(),
            'recentWaitlists' => Waitlist::latest()->limit(8)->get(),
            'totalApplications' => Application::count(),
            'submittedApplications' => Application::where('status', 'submitted')->count(),
            'interviewApplications' => Application::where('status', 'interview')->count(),
            'hiredApplications' => Application::where('status', 'hired')->count(),
            'recentApplications' => Application::with(['candidateProfile', 'jobPost', 'company'])
                ->latest('applied_at')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
