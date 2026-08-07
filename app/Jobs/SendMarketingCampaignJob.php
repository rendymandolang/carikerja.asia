<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Services\MarketingCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMarketingCampaignJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(MarketingCampaignService $campaignService): void
    {
        $campaign = MarketingCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $campaignService->deliver($campaign);
    }

    public function failed(?Throwable $exception): void
    {
        MarketingCampaign::query()
            ->whereKey($this->campaignId)
            ->update([
                'status' => 'failed',
                'finished_at' => now(),
                'last_error' => $exception?->getMessage() ?: 'Campaign job failed.',
            ]);
    }
}
