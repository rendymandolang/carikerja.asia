<?php

use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEmailCenterController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminJobPostController;
use App\Http\Controllers\Admin\AdminJobReportController;
use App\Http\Controllers\Admin\AdminMarketingCampaignController;
use App\Http\Controllers\Admin\AdminRecruiterController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminWaitlistController;
use App\Http\Controllers\Auth\PortalPasswordResetController;
use App\Http\Controllers\Candidate\ApplicationMessageController as CandidateApplicationMessageController;
use App\Http\Controllers\Candidate\CandidateAuthController;
use App\Http\Controllers\Candidate\CandidateGoogleAuthController;
use App\Http\Controllers\Candidate\CandidateInterviewController;
use App\Http\Controllers\Candidate\CandidatePortalController;
use App\Http\Controllers\Candidate\CandidateProfileController;
use App\Http\Controllers\EmailUnsubscribeController;
use App\Http\Controllers\Frontend\CompanyController;
use App\Http\Controllers\Frontend\JobApplicationController;
use App\Http\Controllers\Frontend\JobBoardController;
use App\Http\Controllers\Frontend\JobReportController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\Recruiter\ApplicationInterviewController;
use App\Http\Controllers\Recruiter\ApplicationMessageController as RecruiterApplicationMessageController;
use App\Http\Controllers\Recruiter\GoogleWorkspaceController;
use App\Http\Controllers\Recruiter\RecruiterAuthController;
use App\Http\Controllers\Recruiter\RecruiterPortalController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WaitlistController::class, 'landing'])->name('landing');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
Route::get('/email/unsubscribe/{token}', EmailUnsubscribeController::class)->name('email.unsubscribe');
Route::get('/privacy-policy', [LegalPageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms-of-service', [LegalPageController::class, 'terms'])->name('legal.terms');
Route::get('/cookie-policy', [LegalPageController::class, 'cookies'])->name('legal.cookies');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/jobs', [JobBoardController::class, 'index'])->name('jobs.index');
Route::get('/jobs/city/{citySlug}', [JobBoardController::class, 'city'])->name('jobs.city');
Route::get('/jobs/category/{employmentType}', [JobBoardController::class, 'category'])->name('jobs.category');
Route::get('/jobs/{jobPost:slug}/apply', [JobApplicationController::class, 'create'])->name('jobs.apply.create');
Route::post('/jobs/{jobPost:slug}/apply', [JobApplicationController::class, 'store'])->name('jobs.apply.store');
Route::post('/jobs/{jobPost:slug}/report', [JobReportController::class, 'store'])->middleware('throttle:5,60')->name('jobs.report');
Route::get('/jobs/{jobPost:slug}', [JobBoardController::class, 'show'])->name('jobs.show');
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{company:slug}', [CompanyController::class, 'show'])->name('companies.show');

Route::prefix('candidate')->group(function () {
    Route::get('/login', [CandidateAuthController::class, 'showLogin'])->name('candidate.login');
    Route::post('/login', [CandidateAuthController::class, 'login'])->name('candidate.login.submit');
    Route::get('/password/forgot', [PortalPasswordResetController::class, 'showForgot'])->defaults('portal', 'candidate')->name('candidate.password.request');
    Route::post('/password/email', [PortalPasswordResetController::class, 'sendResetLink'])->defaults('portal', 'candidate')->name('candidate.password.email');
    Route::get('/password/reset/{token}', [PortalPasswordResetController::class, 'showReset'])->defaults('portal', 'candidate')->name('candidate.password.reset');
    Route::post('/password/reset', [PortalPasswordResetController::class, 'reset'])->defaults('portal', 'candidate')->name('candidate.password.update');
    Route::get('/login/google', [CandidateGoogleAuthController::class, 'redirect'])->name('candidate.login.google');
    Route::get('/login/google/callback', [CandidateGoogleAuthController::class, 'callback'])->name('candidate.login.google.callback');

    Route::middleware('candidate')->group(function () {
        Route::post('/logout', [CandidateAuthController::class, 'logout'])->name('candidate.logout');
        Route::get('/dashboard', [CandidatePortalController::class, 'dashboard'])->name('candidate.dashboard');
        Route::get('/account/security', [AccountSecurityController::class, 'edit'])->defaults('portal', 'candidate')->name('candidate.account.security.edit');
        Route::put('/account/security', [AccountSecurityController::class, 'update'])->defaults('portal', 'candidate')->name('candidate.account.security.update');
        Route::get('/notifications', [UserNotificationController::class, 'candidateIndex'])->name('candidate.notifications.index');
        Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('candidate.notifications.read-all');
        Route::post('/notifications/{notificationId}/read', [UserNotificationController::class, 'markRead'])->name('candidate.notifications.read');
        Route::get('/profile', [CandidateProfileController::class, 'edit'])->name('candidate.profile.edit');
        Route::put('/profile', [CandidateProfileController::class, 'update'])->name('candidate.profile.update');
        Route::post('/profile/resume', [CandidateProfileController::class, 'updateResume'])->name('candidate.profile.resume.update');
        Route::get('/profile/resume', [CandidateProfileController::class, 'downloadResume'])->name('candidate.profile.resume.download');
        Route::post('/profile/experiences', [CandidateProfileController::class, 'storeExperience'])->name('candidate.profile.experiences.store');
        Route::put('/profile/experiences/{experience}', [CandidateProfileController::class, 'updateExperience'])->name('candidate.profile.experiences.update');
        Route::delete('/profile/experiences/{experience}', [CandidateProfileController::class, 'deleteExperience'])->name('candidate.profile.experiences.delete');
        Route::post('/profile/educations', [CandidateProfileController::class, 'storeEducation'])->name('candidate.profile.educations.store');
        Route::put('/profile/educations/{education}', [CandidateProfileController::class, 'updateEducation'])->name('candidate.profile.educations.update');
        Route::delete('/profile/educations/{education}', [CandidateProfileController::class, 'deleteEducation'])->name('candidate.profile.educations.delete');
        Route::post('/profile/skills', [CandidateProfileController::class, 'storeSkill'])->name('candidate.profile.skills.store');
        Route::put('/profile/skills/{skill}', [CandidateProfileController::class, 'updateSkill'])->name('candidate.profile.skills.update');
        Route::delete('/profile/skills/{skill}', [CandidateProfileController::class, 'deleteSkill'])->name('candidate.profile.skills.delete');
        Route::get('/job-matches', [CandidateProfileController::class, 'jobMatches'])->name('candidate.job-matches.index');
        Route::get('/interviews', [CandidateInterviewController::class, 'index'])->name('candidate.interviews.index');
        Route::get('/applications', [CandidatePortalController::class, 'applications'])->name('candidate.applications.index');
        Route::get('/applications/{application}', [CandidatePortalController::class, 'show'])->name('candidate.applications.show');
        Route::post('/applications/{application}/messages', [CandidateApplicationMessageController::class, 'store'])->name('candidate.applications.messages.store');
    });
});

Route::prefix('recruiter')->group(function () {
    Route::get('/login', [RecruiterAuthController::class, 'showLogin'])->name('recruiter.login');
    Route::post('/login', [RecruiterAuthController::class, 'login'])->name('recruiter.login.submit');
    Route::get('/password/forgot', [PortalPasswordResetController::class, 'showForgot'])->defaults('portal', 'recruiter')->name('recruiter.password.request');
    Route::post('/password/email', [PortalPasswordResetController::class, 'sendResetLink'])->defaults('portal', 'recruiter')->name('recruiter.password.email');
    Route::get('/password/reset/{token}', [PortalPasswordResetController::class, 'showReset'])->defaults('portal', 'recruiter')->name('recruiter.password.reset');
    Route::post('/password/reset', [PortalPasswordResetController::class, 'reset'])->defaults('portal', 'recruiter')->name('recruiter.password.update');

    Route::middleware('recruiter')->group(function () {
        Route::post('/logout', [RecruiterAuthController::class, 'logout'])->name('recruiter.logout');
        Route::get('/dashboard', [RecruiterPortalController::class, 'dashboard'])->name('recruiter.dashboard');
        Route::get('/account/security', [AccountSecurityController::class, 'edit'])->defaults('portal', 'recruiter')->name('recruiter.account.security.edit');
        Route::put('/account/security', [AccountSecurityController::class, 'update'])->defaults('portal', 'recruiter')->name('recruiter.account.security.update');
        Route::get('/notifications', [UserNotificationController::class, 'recruiterIndex'])->name('recruiter.notifications.index');
        Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('recruiter.notifications.read-all');
        Route::post('/notifications/{notificationId}/read', [UserNotificationController::class, 'markRead'])->name('recruiter.notifications.read');
        Route::get('/google-workspace/connect', [GoogleWorkspaceController::class, 'redirect'])->name('recruiter.google-workspace.redirect');
        Route::get('/google-workspace/callback', [GoogleWorkspaceController::class, 'callback'])->name('recruiter.google-workspace.callback');
        Route::delete('/google-workspace', [GoogleWorkspaceController::class, 'disconnect'])->name('recruiter.google-workspace.disconnect');

        Route::get('/jobs', [RecruiterPortalController::class, 'jobs'])->name('recruiter.jobs.index');
        Route::get('/jobs/create', [RecruiterPortalController::class, 'createJob'])->name('recruiter.jobs.create');
        Route::post('/jobs', [RecruiterPortalController::class, 'storeJob'])->name('recruiter.jobs.store');
        Route::get('/jobs/{jobPost}', [RecruiterPortalController::class, 'showJob'])->name('recruiter.jobs.show');
        Route::get('/jobs/{jobPost}/edit', [RecruiterPortalController::class, 'editJob'])->name('recruiter.jobs.edit');
        Route::put('/jobs/{jobPost}', [RecruiterPortalController::class, 'updateJob'])->name('recruiter.jobs.update');
        Route::post('/jobs/{jobPost}/confirm', [RecruiterPortalController::class, 'confirmJob'])->name('recruiter.jobs.confirm');

        Route::get('/applications', [RecruiterPortalController::class, 'applications'])->name('recruiter.applications.index');
        Route::get('/applications/{application}', [RecruiterPortalController::class, 'showApplication'])->name('recruiter.applications.show');
        Route::get('/applications/{application}/resume', [RecruiterPortalController::class, 'downloadResume'])->name('recruiter.applications.resume');
        Route::patch('/applications/{application}', [RecruiterPortalController::class, 'updateApplication'])->name('recruiter.applications.update');
        Route::post('/applications/{application}/interviews', [ApplicationInterviewController::class, 'store'])->name('recruiter.applications.interviews.store');
        Route::post('/applications/{application}/messages', [RecruiterApplicationMessageController::class, 'store'])->name('recruiter.applications.messages.store');
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::get('/password/forgot', [PortalPasswordResetController::class, 'showForgot'])->defaults('portal', 'admin')->name('admin.password.request');
    Route::post('/password/email', [PortalPasswordResetController::class, 'sendResetLink'])->defaults('portal', 'admin')->name('admin.password.email');
    Route::get('/password/reset/{token}', [PortalPasswordResetController::class, 'showReset'])->defaults('portal', 'admin')->name('admin.password.reset');
    Route::post('/password/reset', [PortalPasswordResetController::class, 'reset'])->defaults('portal', 'admin')->name('admin.password.update');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/account/security', [AccountSecurityController::class, 'edit'])->defaults('portal', 'admin')->name('admin.account.security.edit');
        Route::put('/account/security', [AccountSecurityController::class, 'update'])->defaults('portal', 'admin')->name('admin.account.security.update');

        Route::get('/waitlists', [AdminWaitlistController::class, 'index'])->name('admin.waitlists.index');
        Route::get('/waitlists/export', [AdminWaitlistController::class, 'export'])->name('admin.waitlists.export');
        Route::get('/waitlists/{waitlist}', [AdminWaitlistController::class, 'show'])->name('admin.waitlists.show');
        Route::patch('/waitlists/{waitlist}', [AdminWaitlistController::class, 'update'])->name('admin.waitlists.update');

        Route::get('/companies', [AdminCompanyController::class, 'index'])->name('admin.companies.index');
        Route::get('/companies/create', [AdminCompanyController::class, 'create'])->name('admin.companies.create');
        Route::post('/companies', [AdminCompanyController::class, 'store'])->name('admin.companies.store');
        Route::get('/companies/{company}', [AdminCompanyController::class, 'show'])->name('admin.companies.show');
        Route::get('/companies/{company}/edit', [AdminCompanyController::class, 'edit'])->name('admin.companies.edit');
        Route::put('/companies/{company}', [AdminCompanyController::class, 'update'])->name('admin.companies.update');

        Route::get('/recruiters', [AdminRecruiterController::class, 'index'])->name('admin.recruiters.index');
        Route::get('/recruiters/create', [AdminRecruiterController::class, 'create'])->name('admin.recruiters.create');
        Route::post('/recruiters', [AdminRecruiterController::class, 'store'])->name('admin.recruiters.store');
        Route::get('/recruiters/{recruiter}', [AdminRecruiterController::class, 'show'])->name('admin.recruiters.show');
        Route::get('/recruiters/{recruiter}/edit', [AdminRecruiterController::class, 'edit'])->name('admin.recruiters.edit');
        Route::put('/recruiters/{recruiter}', [AdminRecruiterController::class, 'update'])->name('admin.recruiters.update');

        Route::get('/jobs', [AdminJobPostController::class, 'index'])->name('admin.jobs.index');
        Route::get('/jobs/create', [AdminJobPostController::class, 'create'])->name('admin.jobs.create');
        Route::post('/jobs', [AdminJobPostController::class, 'store'])->name('admin.jobs.store');
        Route::get('/jobs/{jobPost}', [AdminJobPostController::class, 'show'])->name('admin.jobs.show');
        Route::get('/jobs/{jobPost}/edit', [AdminJobPostController::class, 'edit'])->name('admin.jobs.edit');
        Route::put('/jobs/{jobPost}', [AdminJobPostController::class, 'update'])->name('admin.jobs.update');
        Route::patch('/job-reports/{jobReport}', [AdminJobReportController::class, 'update'])->name('admin.job-reports.update');

        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
        Route::get('/applications/{application}/resume', [AdminApplicationController::class, 'downloadResume'])->name('admin.applications.resume');
        Route::patch('/applications/{application}', [AdminApplicationController::class, 'update'])->name('admin.applications.update');

        Route::get('/email', [AdminEmailCenterController::class, 'index'])->name('admin.email.index');
        Route::post('/email/test', [AdminEmailCenterController::class, 'sendTest'])->name('admin.email.test');
        Route::get('/email/templates', [AdminEmailTemplateController::class, 'index'])->name('admin.email.templates.index');
        Route::get('/email/templates/create', [AdminEmailTemplateController::class, 'create'])->name('admin.email.templates.create');
        Route::post('/email/templates', [AdminEmailTemplateController::class, 'store'])->name('admin.email.templates.store');
        Route::get('/email/templates/{template}/edit', [AdminEmailTemplateController::class, 'edit'])->name('admin.email.templates.edit');
        Route::put('/email/templates/{template}', [AdminEmailTemplateController::class, 'update'])->name('admin.email.templates.update');
        Route::delete('/email/templates/{template}', [AdminEmailTemplateController::class, 'destroy'])->name('admin.email.templates.destroy');
        Route::get('/email/campaigns', [AdminMarketingCampaignController::class, 'index'])->name('admin.email.campaigns.index');
        Route::get('/email/campaigns/create', [AdminMarketingCampaignController::class, 'create'])->name('admin.email.campaigns.create');
        Route::post('/email/campaigns', [AdminMarketingCampaignController::class, 'store'])->name('admin.email.campaigns.store');
        Route::get('/email/campaigns/{campaign}', [AdminMarketingCampaignController::class, 'show'])->name('admin.email.campaigns.show');
        Route::get('/email/campaigns/{campaign}/edit', [AdminMarketingCampaignController::class, 'edit'])->name('admin.email.campaigns.edit');
        Route::put('/email/campaigns/{campaign}', [AdminMarketingCampaignController::class, 'update'])->name('admin.email.campaigns.update');
        Route::post('/email/campaigns/{campaign}/test', [AdminMarketingCampaignController::class, 'sendTest'])->name('admin.email.campaigns.test');
        Route::post('/email/campaigns/{campaign}/schedule', [AdminMarketingCampaignController::class, 'schedule'])->name('admin.email.campaigns.schedule');
        Route::post('/email/campaigns/{campaign}/cancel-schedule', [AdminMarketingCampaignController::class, 'cancelSchedule'])->name('admin.email.campaigns.cancel-schedule');
        Route::post('/email/campaigns/{campaign}/send', [AdminMarketingCampaignController::class, 'send'])->name('admin.email.campaigns.send');

        Route::get('/system', [AdminSystemController::class, 'index'])->name('admin.system.index');
        Route::post('/system/backups/run', [AdminSystemController::class, 'runBackup'])->name('admin.system.backups.run');
        Route::post('/system/queue/run-once', [AdminSystemController::class, 'runQueueOnce'])->name('admin.system.queue.run-once');

    });
});
