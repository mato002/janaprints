<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Enums\InboxConversationType;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationParticipant;
use App\Models\Communications\Inbox\CommunicationConversationStatusHistory;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InboxConversationService
{
    public function __construct(
        protected InboxLogSyncService $sync,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = CommunicationConversation::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['customer', 'assignee', 'owner']);

        $view = $filters['view'] ?? 'all';
        match ($view) {
            'my' => $query->where(function (Builder $q) {
                $uid = auth()->id();
                $q->where('assigned_user_id', $uid)->orWhere('owner_user_id', $uid);
            }),
            'unassigned' => $query->whereNull('assigned_user_id')
                ->whereIn('status', InboxConversationStatus::activeValues()),
            'unread' => $query->where('unread_count', '>', 0),
            'open' => $query->where('status', InboxConversationStatus::Open),
            'pending' => $query->where('status', InboxConversationStatus::Pending),
            'waiting_customer' => $query->where('status', InboxConversationStatus::WaitingCustomer),
            'waiting_internal' => $query->where('status', InboxConversationStatus::WaitingInternal),
            'pending_approval' => $query->where('status', InboxConversationStatus::PendingApproval),
            'resolved' => $query->where('status', InboxConversationStatus::Resolved),
            'closed' => $query->where('status', InboxConversationStatus::Closed),
            'archived' => $query->where('status', InboxConversationStatus::Archived),
            'escalated' => $query->where(function (Builder $q) {
                $q->where('is_escalated', true)->orWhere('status', InboxConversationStatus::Escalated);
            }),
            'overdue' => $query->where('sla_status', 'red')
                ->whereIn('status', InboxConversationStatus::activeValues()),
            'waiting' => $query->where('waiting_since', '<=', now()->subHours(24)),
            'customer' => $query->where('conversation_type', InboxConversationType::Customer),
            'supplier' => $query->where('conversation_type', InboxConversationType::Supplier),
            'employee' => $query->where('conversation_type', InboxConversationType::Employee),
            default => null,
        };

        if ($type = $filters['conversation_type'] ?? null) {
            $query->where('conversation_type', $type);
        }

        if ($assigned = $filters['assigned_user_id'] ?? null) {
            $query->where('assigned_user_id', $assigned);
        }

        if ($status = $filters['status'] ?? null) {
            if ($enum = InboxConversationStatus::tryFrom($status)) {
                $query->where('status', $enum);
            }
        }

        if ($tag = trim((string) ($filters['tag'] ?? ''))) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function (Builder $inner) use ($q, $companyId) {
                $inner->where('display_name', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('conversation_code', 'like', "%{$q}%")
                    ->orWhere('last_message_preview', 'like', "%{$q}%")
                    ->orWhereHas('threadMessages', fn (Builder $m) => $m->where('body', 'like', "%{$q}%"))
                    ->orWhereHas('customer', fn (Builder $c) => $c
                        ->where('company_name', 'like', "%{$q}%")
                        ->orWhere('customer_code', 'like', "%{$q}%"))
                    ->orWhereIn('quotation_id', Quotation::query()->where('company_id', $companyId)
                        ->where('quotation_number', 'like', "%{$q}%")->pluck('id'))
                    ->orWhereIn('sales_order_id', SalesOrder::query()->where('company_id', $companyId)
                        ->where('order_number', 'like', "%{$q}%")->pluck('id'))
                    ->orWhereIn('production_job_card_id', ProductionJobCard::query()->where('company_id', $companyId)
                        ->where('job_card_number', 'like', "%{$q}%")->pluck('id'))
                    ->orWhereIn('customer_invoice_id', CustomerInvoice::query()->where('company_id', $companyId)
                        ->where('invoice_number', 'like', "%{$q}%")->pluck('id'))
                    ->orWhereIn('customer_payment_id', CustomerPayment::query()->where('company_id', $companyId)
                        ->where(function (Builder $p) use ($q) {
                            $p->where('payment_number', 'like', "%{$q}%")
                                ->orWhere('reference', 'like', "%{$q}%");
                        })->pluck('id'));
            });
        }

        return $query->orderByDesc('last_activity_at')->orderByDesc('id');
    }

    public function findOrCreateForCustomer(Customer $customer, int $userId): CommunicationConversation
    {
        $existing = CommunicationConversation::query()
            ->where('company_id', $customer->company_id)
            ->where('conversation_type', InboxConversationType::Customer)
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [InboxConversationStatus::Archived])
            ->orderByDesc('last_activity_at')
            ->first();

        if ($existing) {
            $this->sync->syncCommunicationLogs($existing);

            return $existing;
        }

        $conversation = CommunicationConversation::query()->create([
            'company_id' => $customer->company_id,
            'branch_id' => $customer->branch_id,
            'conversation_code' => $this->nextCode($customer->company_id),
            'conversation_type' => InboxConversationType::Customer,
            'status' => InboxConversationStatus::Open,
            'customer_id' => $customer->id,
            'display_name' => $customer->name,
            'phone_number' => $customer->phone,
            'email' => $customer->email,
            'started_at' => now(),
            'last_activity_at' => now(),
            'waiting_since' => now(),
            'created_by' => $userId,
        ]);

        CommunicationConversationParticipant::query()->create([
            'communication_conversation_id' => $conversation->id,
            'participant_type' => 'customer',
            'participant_id' => $customer->id,
            'display_name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'role' => 'contact',
        ]);

        $this->recordStatus($conversation, null, InboxConversationStatus::Open, 'created', $userId);
        $this->sync->syncCommunicationLogs($conversation);

        return $conversation;
    }

    /**
     * @return Collection<int, CommunicationConversation>
     */
    public function forCustomer(int $companyId, int $customerId, int $limit = 10): Collection
    {
        return $this->query($companyId, ['conversation_type' => InboxConversationType::Customer->value])
            ->where('customer_id', $customerId)
            ->limit($limit)
            ->get();
    }

    public function markRead(CommunicationConversation $conversation): void
    {
        $conversation->update(['unread_count' => 0]);
    }

    public function touchActivity(CommunicationConversation $conversation, string $preview, ?string $channel = null): void
    {
        $conversation->update([
            'last_message_preview' => mb_substr($preview, 0, 200),
            'last_activity_at' => now(),
            'last_channel' => $channel,
        ]);
    }

    public function recordStatus(
        CommunicationConversation $conversation,
        ?InboxConversationStatus $from,
        InboxConversationStatus $to,
        string $event,
        ?int $userId = null,
        ?array $payload = null,
    ): void {
        CommunicationConversationStatusHistory::query()->create([
            'communication_conversation_id' => $conversation->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'event' => $event,
            'payload' => $payload,
            'created_by' => $userId,
        ]);
    }

    protected function nextCode(int $companyId): string
    {
        $count = CommunicationConversation::query()->where('company_id', $companyId)->count() + 1;

        return 'INB-'.now()->format('ymd').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
