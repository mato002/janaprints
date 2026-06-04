<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsCampaignStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SmsDashboardPresenter
{
    public function __construct(
        protected SmsCreditService $credits,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId): array
    {
        $balance = $this->credits->balanceFor($companyId);
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $messagesBase = SmsMessage::query()->where('company_id', $companyId);

        $sentToday = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Sent)
            ->whereDate('sent_at', $today)->count();

        $sentMonth = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Sent)
            ->where('sent_at', '>=', $monthStart)->count();

        $failed = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Failed)->count();
        $queued = (clone $messagesBase)->where('queue_status', SmsMessageQueueStatus::Queued)->count();

        $campaignsMonth = SmsCampaign::query()->where('company_id', $companyId)
            ->where('created_at', '>=', $monthStart)->count();

        $dailyUsage = (clone $messagesBase)
            ->select(DB::raw('DATE(sent_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('queue_status', SmsMessageQueueStatus::Sent)
            ->where('sent_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $monthlyUsage = (clone $messagesBase)
            ->select(DB::raw('DATE_FORMAT(sent_at, "%Y-%m") as month'), DB::raw('COUNT(*) as total'))
            ->where('queue_status', SmsMessageQueueStatus::Sent)
            ->where('sent_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $delivered = (clone $messagesBase)->whereIn('delivery_status', ['delivered', 'sent'])->count();
        $totalAttempts = max(1, (clone $messagesBase)->whereNotNull('delivery_status')->count());
        $successRate = (int) round(($delivered / $totalAttempts) * 100);

        return [
            'credits_remaining' => (float) $balance->remaining_credits,
            'cost_per_sms' => (float) $balance->cost_per_sms,
            'sent_today' => $sentToday,
            'sent_month' => $sentMonth,
            'failed_messages' => $failed,
            'queued_messages' => $queued,
            'campaigns_month' => $campaignsMonth,
            'daily_usage' => $dailyUsage,
            'monthly_usage' => $monthlyUsage,
            'delivery_success_rate' => $successRate,
            'recent_campaigns' => SmsCampaign::query()
                ->where('company_id', $companyId)
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'status', 'sent_count', 'failed_count', 'created_at']),
        ];
    }
}
