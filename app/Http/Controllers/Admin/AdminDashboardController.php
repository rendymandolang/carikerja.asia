<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\JobReport;
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
            'overdueApplications' => Application::with(['candidateProfile', 'jobPost', 'company'])->whereNull('first_responded_at')->whereNull('finalized_at')->where('response_due_at', '<=', now())->oldest('response_due_at')->limit(10)->get(),
            'overdueApplicationCount' => Application::whereNull('first_responded_at')->whereNull('finalized_at')->where('response_due_at', '<=', now())->count(),
            'jobsDueConfirmation' => JobPost::with('company')->where('status', 'published')->where('confirmation_due_at', '<=', now()->addDays(7))->orderBy('confirmation_due_at')->limit(10)->get(),
            'openJobReports' => JobReport::with(['jobPost.company'])->whereIn('status', ['new', 'reviewing'])->latest()->limit(10)->get(),
            'unresponsiveCompanies' => Company::where('response_sample_size', '>', 0)->where(fn ($q) => $q->where('response_rate', '<', config('hiring.active_responder_rate', 80))->orWhereNull('last_recruiter_activity_at')->orWhere('last_recruiter_activity_at', '<', now()->subDays(config('hiring.active_responder_days', 14))))->orderBy('response_rate')->limit(10)->get(),
        ]);
    }
}
