<?php

namespace App\Jobs\Communications;

use App\Jobs\PlatformJob;
use App\Models\Communications\SmsCampaign;
use App\Support\Communications\Sms\SmsCampaignService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSmsCampaignJob extends PlatformJob implements ShouldQueue
{
    public function __construct(
        public int $campaignId,
    ) {
        parent::__construct();
        $this->useQueue('sms');
    }

    public function handle(SmsCampaignService $campaigns): void
    {
        $campaign = SmsCampaign::query()->find($this->campaignId);

        if ($campaign === null || $campaign->status->value === 'cancelled') {
            return;
        }

        $campaigns->buildMessages($campaign);

        foreach ($campaign->fresh()->messages as $message) {
            SendSmsMessageJob::dispatch($message->id);
        }
    }
}
