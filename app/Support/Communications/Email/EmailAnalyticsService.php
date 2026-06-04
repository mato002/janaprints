<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $companyId): array
    {
        $base = EmailMessage::query()->where('company_id', $companyId);
        $total = (clone $base)->where('status', '!=', EmailDeliveryStatus::Draft)->count();
        $delivered = (clone $base)->whereIn('status', [
            EmailDeliveryStatus::Delivered,
            EmailDeliveryStatus::Opened,
            EmailDeliveryStatus::Clicked,
            EmailDeliveryStatus::Sent,
        ])->count();
        $opened = (clone $base)->whereIn('status', [EmailDeliveryStatus::Opened, EmailDeliveryStatus::Clicked])->count();
        $clicked = (clone $base)->where('status', EmailDeliveryStatus::Clicked)->count();
        $bounced = (clone $base)->where('status', EmailDeliveryStatus::Bounced)->count();
        $failed = (clone $base)->where('status', EmailDeliveryStatus::Failed)->count();

        $daily = (clone $base)
            ->where('created_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $monthly = (clone $base)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        return [
            'sent_today' => (clone $base)->whereDate('sent_at', today())->count(),
            'sent_month' => (clone $base)->where('sent_at', '>=', now()->startOfMonth())->count(),
            'total_sent' => $total,
            'open_rate' => $delivered > 0 ? (int) round(($opened / $delivered) * 100) : 0,
            'click_rate' => $delivered > 0 ? (int) round(($clicked / $delivered) * 100) : 0,
            'bounce_rate' => $total > 0 ? (int) round(($bounced / $total) * 100) : 0,
            'failed_count' => $failed,
            'delivery_success_rate' => $total > 0 ? (int) round(($delivered / $total) * 100) : 0,
            'daily_activity' => $daily,
            'monthly_activity' => $monthly,
        ];
    }
}
