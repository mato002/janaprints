<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsMessageQueueStatus;
use App\Models\Communications\SmsCreditTransaction;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsProviderLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SmsDashboardPresenter
{
    public function __construct(
        protected SmsCreditService $credits,
        protected SmsCrmWalletTopupService $topups,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId): array
    {
        $live = $this->topups->liveBalance($companyId);
        $balance = $this->credits->balanceFor($companyId);
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $creditsRemaining = (float) ($live['balance'] ?? $balance->remaining_credits);
        $costPerSms = (float) ($live['price_per_unit'] ?? $balance->cost_per_sms ?: 1);
        $lowCreditThreshold = max(100, $costPerSms * 100);

        $messagesBase = SmsMessage::query()->where('company_id', $companyId);

        $sentToday = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Sent)
            ->whereDate('sent_at', $today)->count();

        $sentMonth = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Sent)
            ->where('sent_at', '>=', $monthStart)->count();

        $failed = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Failed)->count();
        $queued = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Queued)->count();
        $processing = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Processing)->count();

        $dailyUsage = (clone $messagesBase)
            ->select(DB::raw('DATE(sent_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('queue_status', SmsMessageQueueStatus::Sent)
            ->where('sent_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $delivered = (clone $messagesBase)->whereIn('delivery_status', ['delivered', 'sent'])->count();
        $totalAttempts = (clone $messagesBase)->whereNotNull('delivery_status')->count();
        $successRate = $totalAttempts > 0 ? (int) round(($delivered / $totalAttempts) * 100) : null;

        $approxMessagesLeft = $costPerSms > 0
            ? (int) floor($creditsRemaining / $costPerSms)
            : (int) $creditsRemaining;

        $lastProviderLog = SmsProviderLog::query()
            ->whereHas('message', fn ($q) => $q->where('company_id', $companyId))
            ->latest('id')
            ->first(['id', 'http_status', 'provider', 'created_at', 'response_payload']);

        $attention = [];

        if ($creditsRemaining <= $lowCreditThreshold) {
            $attention[] = [
                'tone' => 'warning',
                'title' => __('SMS credits are running low'),
                'detail' => __('About :count message segments remaining at the current rate.', ['count' => number_format($approxMessagesLeft)]),
                'action_label' => __('Top up credits'),
                'action_anchor' => 'sms-topup',
            ];
        }

        if ($failed > 0) {
            $attention[] = [
                'tone' => 'danger',
                'title' => __(':count failed messages need review', ['count' => number_format($failed)]),
                'detail' => __('Open the queue and retry or fix delivery issues.'),
                'action_label' => __('Open failed queue'),
                'action_url' => route('admin.communications.sms.queues.index', ['queue_status' => 'failed'], absolute: false),
            ];
        }

        if ($queued + $processing > 50) {
            $attention[] = [
                'tone' => 'warning',
                'title' => __(':count messages still in queue', ['count' => number_format($queued + $processing)]),
                'detail' => __('Sending may be delayed — check the queue and provider status.'),
                'action_label' => __('View queue'),
                'action_url' => route('admin.communications.sms.queues.index', absolute: false),
            ];
        }

        if ($lastProviderLog && (int) $lastProviderLog->http_status >= 400) {
            $attention[] = [
                'tone' => 'danger',
                'title' => __('Latest provider call returned HTTP :status', ['status' => $lastProviderLog->http_status]),
                'detail' => __('Provider: :provider. Review the gateway logs.', ['provider' => $lastProviderLog->provider ?? __('unknown')]),
                'action_label' => __('Provider logs'),
                'action_url' => route('admin.communications.sms.provider-logs.index', absolute: false),
            ];
        }

        return [
            'credits_remaining' => $creditsRemaining,
            'cost_per_sms' => $costPerSms,
            'credit_source' => (string) ($live['source'] ?? 'local'),
            'credit_currency' => (string) ($live['currency'] ?? config('pradytec_sms.currency', 'KES')),
            'approx_messages_left' => $approxMessagesLeft,
            'low_credit' => $creditsRemaining <= $lowCreditThreshold,
            'sent_today' => $sentToday,
            'sent_month' => $sentMonth,
            'failed_messages' => $failed,
            'queued_messages' => $queued,
            'processing_messages' => $processing,
            'daily_usage' => $dailyUsage,
            'delivery_success_rate' => $successRate,
            'attention' => $attention,
            'recent_transactions' => SmsCreditTransaction::query()
                ->where('company_id', $companyId)
                ->with(['campaign:id,name'])
                ->latest('id')
                ->limit(8)
                ->get(),
            'last_provider_log' => $lastProviderLog,
        ];
    }
}
