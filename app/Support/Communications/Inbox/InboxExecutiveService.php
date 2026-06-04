<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Enums\InboxSlaStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InboxExecutiveService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $companyId): array
    {
        $base = CommunicationConversation::query()->where('company_id', $companyId);
        $activeStatuses = InboxConversationStatus::activeValues();

        $today = (clone $base)->whereDate('last_activity_at', today());

        return [
            'open' => (clone $base)->where('status', InboxConversationStatus::Open)->count(),
            'waiting_customer' => (clone $base)->where('status', InboxConversationStatus::WaitingCustomer)->count(),
            'waiting_internal' => (clone $base)->where('status', InboxConversationStatus::WaitingInternal)->count(),
            'escalated' => (clone $base)->where(function ($q) {
                $q->where('is_escalated', true)->orWhere('status', InboxConversationStatus::Escalated);
            })->count(),
            'unassigned' => (clone $base)->whereNull('assigned_user_id')
                ->whereIn('status', $activeStatuses)->count(),
            'overdue' => (clone $base)->where('sla_status', InboxSlaStatus::Red)
                ->whereIn('status', $activeStatuses)->count(),
            'unanswered' => (clone $base)->where('unread_count', '>', 0)
                ->whereIn('status', $activeStatuses)->count(),
            'active' => (clone $base)->whereIn('status', $activeStatuses)->count(),
            'waiting_long' => (clone $base)->where('waiting_since', '<=', now()->subHours(24))
                ->whereIn('status', $activeStatuses)->count(),
            'closed_today' => (clone $base)->where('status', InboxConversationStatus::Closed)
                ->whereDate('closed_at', today())->count(),
            'volume_today' => $today->count(),
            'unread_total' => (clone $base)->sum('unread_count'),
            'avg_first_response_minutes' => $this->avgFirstResponse($companyId),
            'high_value_waiting' => $this->highValueWaiting($companyId),
            'longest_waiting' => $this->longestWaiting($companyId),
            'most_active_customers' => $this->mostActiveCustomers($companyId),
            'vip_waiting' => $this->vipWaiting($companyId),
            'recent_complaints' => (clone $base)->where(function ($q) {
                $q->where('is_escalated', true)->orWhere('status', InboxConversationStatus::Escalated);
            })->orderByDesc('escalated_at')->limit(8)->with(['customer', 'assignee'])->get(),
            'recent_escalated' => (clone $base)->where('is_escalated', true)
                ->orderByDesc('escalated_at')->limit(10)->with(['customer', 'assignee'])->get(),
            'recent_unassigned' => (clone $base)->whereNull('assigned_user_id')
                ->whereIn('status', $activeStatuses)
                ->orderByDesc('last_activity_at')->limit(10)->with('customer')->get(),
        ];
    }

    protected function avgFirstResponse(int $companyId): ?float
    {
        $avg = CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->whereNotNull('first_response_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, first_response_at)) as avg_mins')
            ->value('avg_mins');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    /**
     * @return Collection<int, CommunicationConversation>
     */
    protected function highValueWaiting(int $companyId): Collection
    {
        $customerIds = $this->highValueCustomerIds($companyId);

        return CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->whereIn('customer_id', $customerIds)
            ->whereIn('status', [InboxConversationStatus::WaitingCustomer, InboxConversationStatus::Open])
            ->orderByDesc('last_activity_at')
            ->limit(8)
            ->with('customer')
            ->get();
    }

    /**
     * @return Collection<int, CommunicationConversation>
     */
    protected function longestWaiting(int $companyId): Collection
    {
        return CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->whereIn('status', InboxConversationStatus::activeValues())
            ->whereNotNull('waiting_since')
            ->orderBy('waiting_since')
            ->limit(8)
            ->with(['customer', 'assignee'])
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    protected function mostActiveCustomers(int $companyId): Collection
    {
        return CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as thread_count'), DB::raw('MAX(last_activity_at) as last_at'))
            ->groupBy('customer_id')
            ->orderByDesc('thread_count')
            ->limit(8)
            ->with('customer')
            ->get();
    }

    /**
     * @return Collection<int, CommunicationConversation>
     */
    protected function vipWaiting(int $companyId): Collection
    {
        return CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->whereIn('status', InboxConversationStatus::activeValues())
            ->where(function ($q) {
                $q->whereJsonContains('tags', 'vip')
                    ->orWhereHas('customer.segments', fn ($s) => $s->where('name', 'like', '%VIP%'));
            })
            ->orderByDesc('last_activity_at')
            ->limit(8)
            ->with('customer')
            ->get();
    }

    /**
     * @return list<int>
     */
    protected function highValueCustomerIds(int $companyId): array
    {
        return CustomerInvoice::query()
            ->where('company_id', $companyId)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('SUM(total_amount) >= 500000')
            ->pluck('customer_id')
            ->all();
    }
}
