<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\MarketingCampaign;
use App\Services\MarketingCampaignService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMarketingCampaignController extends Controller
{
    public function index()
    {
        return view('admin.email.campaigns.index', [
            'campaigns' => MarketingCampaign::query()
                ->with(['createdBy', 'template'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(Request $request, MarketingCampaignService $campaignService)
    {
        $campaign = new MarketingCampaign([
            'audience' => 'all_contacts',
            'button_label' => 'Lihat carikerja.asia',
            'button_url' => route('landing'),
        ]);

        if ($template = $this->marketingTemplates()->firstWhere('id', $request->integer('template_id'))) {
            $campaign->forceFill($this->campaignFieldsFromTemplate($template));
        }

        return view('admin.email.campaigns.create', [
            'campaign' => $campaign,
            'audiences' => $this->audiencesWithCounts($campaignService),
            'marketingTemplates' => $this->marketingTemplates(),
        ]);
    }

    public function store(Request $request)
    {
        $campaign = MarketingCampaign::create([
            ...$this->validated($request),
            'status' => 'draft',
            'created_by_user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign marketing berhasil dibuat.');
    }

    public function show(MarketingCampaign $campaign)
    {
        $campaignService = app(MarketingCampaignService::class);

        return view('admin.email.campaigns.show', [
            'campaign' => $campaign->load(['createdBy', 'sentBy', 'template']),
            'audienceCount' => $campaignService->previewCount($campaign->audience),
            'recipients' => $campaign->recipients()->latest()->paginate(25),
        ]);
    }

    public function edit(MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        abort_if(! $campaign->isEditable(), 403);

        return view('admin.email.campaigns.edit', [
            'campaign' => $campaign,
            'audiences' => $this->audiencesWithCounts($campaignService),
            'marketingTemplates' => $this->marketingTemplates(),
        ]);
    }

    public function update(Request $request, MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        abort_if(! $campaign->isEditable(), 403);

        $campaign->update($this->validated($request));

        if ($campaign->status === 'scheduled') {
            $campaignService->schedule($campaign, $request->user());
        }

        return redirect()
            ->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign marketing berhasil diperbarui.');
    }

    public function sendTest(Request $request, MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:150'],
        ]);

        $campaignService->sendTest(
            $campaign,
            $validated['email'],
            $validated['name'] ?: $request->user()->name,
        );

        return back()->with('success', 'Test campaign email dikirim.');
    }

    public function send(Request $request, MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        abort_if(! $campaign->canQueueSend(), 403);

        if ($campaignService->previewCount($campaign->audience) === 0) {
            return back()->withErrors(['audience' => 'Audience kosong atau semua kontak sudah unsubscribe.']);
        }

        $campaign = $campaignService->queueSend($campaign, $request->user());

        return redirect()
            ->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign masuk queue dan akan dikirim oleh worker.');
    }

    public function schedule(Request $request, MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        abort_if(! $campaign->canSchedule(), 403);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        if ($campaignService->previewCount($campaign->audience) === 0) {
            return back()->withErrors(['audience' => 'Audience kosong atau semua kontak sudah unsubscribe.']);
        }

        $campaign->forceFill([
            'scheduled_at' => $validated['scheduled_at'],
        ])->save();

        $campaign = $campaignService->schedule($campaign, $request->user());

        return redirect()
            ->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Campaign berhasil dijadwalkan.');
    }

    public function cancelSchedule(MarketingCampaign $campaign, MarketingCampaignService $campaignService)
    {
        abort_if(! $campaign->canCancelSchedule(), 403);

        $campaign = $campaignService->cancelSchedule($campaign);

        return redirect()
            ->route('admin.email.campaigns.show', $campaign)
            ->with('success', 'Schedule campaign dibatalkan.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'email_template_id' => ['nullable', Rule::exists('email_templates', 'id')->where('category', 'marketing')],
            'name' => ['required', 'string', 'max:150'],
            'audience' => ['required', Rule::in(array_keys(MarketingCampaign::AUDIENCES))],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $validated['email_template_id'] = ($validated['email_template_id'] ?? null) ?: null;
        $validated['scheduled_at'] = ($validated['scheduled_at'] ?? null) ?: null;

        return $validated;
    }

    private function audiencesWithCounts(MarketingCampaignService $campaignService): array
    {
        return collect(MarketingCampaign::AUDIENCES)
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'count' => $campaignService->previewCount($key),
            ])
            ->all();
    }

    private function marketingTemplates()
    {
        return EmailTemplate::query()
            ->where('category', 'marketing')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function campaignFieldsFromTemplate(EmailTemplate $template): array
    {
        return [
            'email_template_id' => $template->id,
            'name' => $template->name,
            'subject' => $template->subject,
            'preheader' => $template->preheader,
            'body' => $template->body,
            'button_label' => $template->button_label,
            'button_url' => $template->button_url,
        ];
    }
}
