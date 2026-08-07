<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailUnsubscribe;
use App\Models\MarketingCampaign;
use App\Services\MarketingCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminEmailCenterController extends Controller
{
    public function index(MarketingCampaignService $campaignService)
    {
        $audienceCounts = collect(MarketingCampaign::AUDIENCES)
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'count' => $campaignService->previewCount($key),
            ]);

        return view('admin.email.index', [
            'mailDefault' => config('mail.default'),
            'mailFromAddress' => config('mail.from.address'),
            'mailFromName' => config('mail.from.name'),
            'templateCount' => EmailTemplate::count(),
            'activeTemplateCount' => EmailTemplate::where('is_active', true)->count(),
            'campaignCount' => MarketingCampaign::count(),
            'sentCampaignCount' => MarketingCampaign::whereIn('status', ['sent', 'sent_with_errors'])->count(),
            'unsubscribedCount' => EmailUnsubscribe::whereNotNull('unsubscribed_at')->count(),
            'recentCampaigns' => MarketingCampaign::with('template')->latest()->limit(5)->get(),
            'audienceCounts' => $audienceCounts,
        ]);
    }

    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        Mail::raw(
            "Ini adalah test email dari Email Center carikerja.asia.\n\nMailer: " . config('mail.default'),
            function ($message) use ($validated) {
                $message
                    ->to($validated['email'])
                    ->subject('Test email carikerja.asia');
            },
        );

        return back()->with('success', 'Test email dikirim melalui mailer saat ini.');
    }
}
