<?php

namespace App\Console\Commands;

use App\Enums\EmailCampaignStatus;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailCampaignService;
use Illuminate\Console\Command;

class DispatchScheduledEmailCampaignsCommand extends Command
{
    protected $signature = 'communications:dispatch-scheduled-email-campaigns';

    protected $description = 'Send email campaigns whose scheduled_at time has arrived.';

    public function handle(EmailCampaignService $campaigns): int
    {
        $due = EmailCampaign::query()
            ->where('status', EmailCampaignStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();

        $sent = 0;

        foreach ($due as $campaign) {
            $actorId = (int) ($campaign->created_by ?: 0);
            if ($actorId <= 0) {
                $this->warn(__('Skipping campaign :code — missing creator.', [
                    'code' => $campaign->campaign_code,
                ]));

                continue;
            }

            $campaigns->send($campaign, $actorId);
            $sent++;
        }

        $this->info(__('Dispatched :count scheduled email campaign(s).', ['count' => $sent]));

        return self::SUCCESS;
    }
}
