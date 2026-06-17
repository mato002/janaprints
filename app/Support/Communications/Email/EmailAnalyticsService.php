<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsService
{
    public function __construct(
        protected EmailVisibilityService $visibility,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $companyId): array
    {
        $base = EmailMessage::query()->where('company_id', $companyId)->where('status', '!=', EmailDeliveryStatus::Draft);

        $sentToday = (clone $base)->whereDate('sent_at', today())->count();
        $failedToday = (clone $base)->whereDate('failed_at', today())->whereIn('status', [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced])->count();
        $queuedToday = (clone $base)->whereIn('status', [EmailDeliveryStatus::Queued, EmailDeliveryStatus::Sending])->count();

        $sentMonth = (clone $base)->where('sent_at', '>=', now()->startOfMonth())->count();
        $failedMonth = (clone $base)->where('failed_at', '>=', now()->startOfMonth())->whereIn('status', [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced])->count();

        $daily = (clone $base)
            ->where('created_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $monthly = (clone $base)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw($this->monthExpression().' as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $topSenders = $this->visibility->topSendersByDepartment($companyId);
        $topRecipients = $this->visibility->topRecipientGroups($companyId);
        $topCustomers = $this->visibility->topCustomersByEmail($companyId, 5);
        $health = $this->visibility->communicationHealth($companyId);

        return [
            'today' => [
                'sent' => $sentToday,
                'failed' => $failedToday,
                'queued' => $queuedToday,
            ],
            'month' => [
                'sent' => $sentMonth,
                'failed' => $failedMonth,
            ],
            'top_senders' => [
                ['key' => 'hr', 'label' => __('HR'), 'count' => $topSenders['hr']],
                ['key' => 'sales', 'label' => __('Sales'), 'count' => $topSenders['sales']],
                ['key' => 'accounts', 'label' => __('Accounts'), 'count' => $topSenders['accounts']],
                ['key' => 'production', 'label' => __('Production'), 'count' => $topSenders['production']],
                ['key' => 'notifications', 'label' => __('Notifications'), 'count' => $topSenders['notifications']],
            ],
            'top_recipients' => [
                ['key' => 'customers', 'label' => __('Customers'), 'count' => $topRecipients['customers']],
                ['key' => 'employees', 'label' => __('Employees'), 'count' => $topRecipients['employees']],
            ],
            'top_customers' => $topCustomers,
            'health' => $health,
            'daily_activity' => $daily,
            'monthly_activity' => $monthly,
        ];
    }

    protected function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : 'DATE_FORMAT(created_at, "%Y-%m")';
    }
}
