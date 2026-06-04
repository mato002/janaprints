<?php

namespace App\Jobs\Communications;

use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Jobs\PlatformJob;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Support\Communications\CommunicationLogService;
use App\Support\Communications\Sms\SmsCampaignService;
use App\Support\Communications\Sms\SmsCreditService;
use App\Support\Communications\Sms\SmsProviderGateway;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSmsMessageJob extends PlatformJob implements ShouldQueue
{
    public int $tries = 3;

    public function __construct(
        public int $messageId,
    ) {
        parent::__construct();
        $this->useQueue('sms');
    }

    public function handle(
        SmsProviderGateway $gateway,
        SmsCreditService $credits,
        SmsCampaignService $campaigns,
        CommunicationLogService $communicationLogs,
    ): void {
        $message = SmsMessage::query()->with('campaign')->find($this->messageId);

        if ($message === null || $message->queue_status === SmsMessageQueueStatus::Cancelled) {
            return;
        }

        $message->update([
            'queue_status' => SmsMessageQueueStatus::Processing,
            'attempts' => $message->attempts + 1,
            'last_attempt_at' => now(),
        ]);

        try {
            $result = $gateway->send($message);

            if ($result['success']) {
                $credits->consume(
                    $message->company_id,
                    (float) $message->credit_cost,
                    $message->sms_campaign_id,
                    $message->id,
                    $message->branch_id,
                    $message->campaign?->department_id,
                );

                $message->update([
                    'queue_status' => SmsMessageQueueStatus::Sent,
                    'delivery_status' => $result['delivery_status'],
                    'sent_at' => now(),
                    'delivered_at' => $result['delivery_status'] === SmsDeliveryStatus::Delivered ? now() : null,
                ]);
            } else {
                $message->update([
                    'queue_status' => SmsMessageQueueStatus::Failed,
                    'delivery_status' => $result['delivery_status'],
                    'failure_reason' => $result['response']['status'] ?? 'rejected',
                ]);
            }
        } catch (\Throwable $e) {
            $message->update([
                'queue_status' => SmsMessageQueueStatus::Failed,
                'delivery_status' => SmsDeliveryStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $message->refresh();
            $communicationLogs->recordFromSmsMessage($message);
            $this->maybeFinalize($message->sms_campaign_id, $campaigns);
        }
    }

    protected function maybeFinalize(int $campaignId, SmsCampaignService $campaigns): void
    {
        $pending = SmsMessage::query()
            ->where('sms_campaign_id', $campaignId)
            ->whereIn('queue_status', [
                SmsMessageQueueStatus::Queued,
                SmsMessageQueueStatus::Processing,
            ])
            ->exists();

        if (! $pending) {
            $campaign = SmsCampaign::query()->find($campaignId);

            if ($campaign && $campaign->status->value === 'sending') {
                $campaigns->finalizeCampaign($campaign);
            }
        }
    }
}
