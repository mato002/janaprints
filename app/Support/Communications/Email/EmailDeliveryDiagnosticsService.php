<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Support\Communications\Bridge\IntegrationProviderResolver;
use App\Support\Communications\Email\Providers\IntegrationBridgedEmailProvider;
use Illuminate\Support\Facades\Queue;

class EmailDeliveryDiagnosticsService
{
    public function __construct(
        protected IntegrationProviderResolver $integrationResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forCompany(int $companyId): array
    {
        $chain = $this->integrationResolver->emailChain($companyId);
        $activeIntegrations = $chain->filter(fn (IntegrationEmailSetting $setting) => $setting->is_active);
        $hasRealProvider = $activeIntegrations->isNotEmpty();
        $queueStatus = $this->queueStatus($companyId);

        return [
            'delivery_engine' => [
                'provider' => IntegrationBridgedEmailProvider::class,
                'active' => $hasRealProvider,
                'label' => $hasRealProvider ? __('Active') : __('Inactive'),
            ],
            'queue' => $queueStatus,
            'smtp' => $this->smtpStatus($companyId, $activeIntegrations),
            'integration' => [
                'enabled' => $activeIntegrations->isNotEmpty(),
                'configured_count' => $chain->count(),
                'active_count' => $activeIntegrations->count(),
                'label' => $activeIntegrations->isNotEmpty()
                    ? __('Enabled')
                    : __('Disabled'),
            ],
            'retention' => [
                'days' => (int) config('communications.retention_days', 3650),
                'auto_delete' => false,
                'label' => __(':days days (policy only)', ['days' => number_format((int) config('communications.retention_days', 3650))]),
            ],
            'recent_failures' => $this->recentMessages($companyId, 'failures'),
            'recent_successes' => $this->recentMessages($companyId, 'successes'),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, IntegrationEmailSetting>  $activeIntegrations
     * @return array{status: string, label: string}
     */
    protected function smtpStatus(int $companyId, $activeIntegrations): array
    {
        if ($activeIntegrations->isNotEmpty()) {
            return [
                'status' => 'configured',
                'label' => __('Configured'),
            ];
        }

        $chain = $this->integrationResolver->emailChain($companyId);

        if ($chain->isEmpty()) {
            return [
                'status' => 'missing',
                'label' => __('Missing'),
            ];
        }

        return [
            'status' => 'inactive',
            'label' => __('Missing'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function queueStatus(int $companyId): array
    {
        $driver = (string) config('queue.default');
        $queueName = (string) config('platform.queues.emails', 'emails');
        $active = $this->emailsQueueActive();
        $stuckMinutes = (int) config('communications.queue.stuck_sending_minutes', 15);

        $base = EmailMessage::query()->where('company_id', $companyId)->where('status', '!=', EmailDeliveryStatus::Draft);

        $queuedCount = (clone $base)->where('status', EmailDeliveryStatus::Queued)->count();
        $sendingCount = (clone $base)->where('status', EmailDeliveryStatus::Sending)->count();
        $failedCount = (clone $base)->whereIn('status', [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced])->count();
        $cancelledCount = (clone $base)->where('status', EmailDeliveryStatus::Cancelled)->count();
        $stuckSending = (clone $base)
            ->where('status', EmailDeliveryStatus::Sending)
            ->where('updated_at', '<', now()->subMinutes($stuckMinutes))
            ->count();

        $depth = 0;

        try {
            if ($driver !== 'sync') {
                $depth = Queue::connection()->size($queueName);
            }
        } catch (\Throwable) {
            $depth = 0;
        }

        return [
            'name' => $queueName,
            'driver' => $driver,
            'active' => $active,
            'label' => $active ? __('Running') : __('Warning'),
            'depth' => $depth,
            'queued_count' => $queuedCount,
            'sending_count' => $sendingCount,
            'stuck_sending' => $stuckSending,
            'failed_count' => $failedCount,
            'cancelled_count' => $cancelledCount,
            'stuck_threshold_minutes' => $stuckMinutes,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentMessages(int $companyId, string $type): array
    {
        $query = EmailMessage::query()
            ->where('company_id', $companyId)
            ->with('account')
            ->orderByDesc('created_at')
            ->limit(10);

        if ($type === 'failures') {
            $query->whereIn('status', [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced]);
        } else {
            $query->whereIn('status', [EmailDeliveryStatus::Sent, EmailDeliveryStatus::Delivered]);
        }

        return $query->get()->map(fn (EmailMessage $message) => [
            'id' => $message->id,
            'subject' => $message->subject,
            'status' => $message->status->label(),
            'sender' => $message->account?->from_email,
            'recipient' => collect($message->to_emails)->pluck('email')->first(),
            'failure_reason' => $message->failure_reason,
            'sent_at' => $message->sent_at?->format('d M Y H:i'),
            'failed_at' => $message->failed_at?->format('d M Y H:i'),
            'created_at' => $message->created_at?->format('d M Y H:i'),
        ])->all();
    }

    protected function emailsQueueActive(): bool
    {
        $driver = (string) config('queue.default');

        if ($driver === 'sync') {
            return true;
        }

        try {
            $connection = Queue::connection();
            $size = $connection->size(config('platform.queues.emails', 'emails'));

            return $size >= 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
