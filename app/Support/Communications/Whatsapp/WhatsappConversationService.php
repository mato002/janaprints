<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappConversationStatus;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappParticipant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WhatsappConversationService
{
    public function __construct(
        protected WhatsappAccountService $accounts,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = WhatsappConversation::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['account', 'customer', 'assignee', 'participants']);

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($assigned = $filters['assigned_user_id'] ?? null) {
            $query->where('assigned_user_id', $assigned);
        }

        if ($branchId = $filters['branch_id'] ?? null) {
            $query->where('branch_id', $branchId);
        }

        if ($accountId = $filters['whatsapp_account_id'] ?? null) {
            $query->where('whatsapp_account_id', $accountId);
        }

        if ($customerId = $filters['customer_id'] ?? null) {
            $query->where('customer_id', $customerId);
        }

        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function (Builder $inner) use ($q) {
                $inner->where('phone_number', 'like', "%{$q}%")
                    ->orWhere('conversation_code', 'like', "%{$q}%")
                    ->orWhere('last_message_preview', 'like', "%{$q}%");
            });
        }

        if (($filters['unread_only'] ?? false) === '1' || ($filters['unread_only'] ?? false) === true) {
            $query->where('unread_count', '>', 0);
        }

        return $query->orderByDesc('last_activity_at')->orderByDesc('id');
    }

    /**
     * @return Collection<int, WhatsappConversation>
     */
    public function forCustomer(int $companyId, int $customerId, int $limit = 10): Collection
    {
        return $this->query($companyId, ['customer_id' => $customerId])->limit($limit)->get();
    }

    public function findOrCreateForContact(
        int $companyId,
        string $phoneNumber,
        int $userId,
        ?int $customerId = null,
        ?int $leadId = null,
        ?int $vendorId = null,
        ?string $displayName = null,
    ): WhatsappConversation {
        $account = $this->accounts->ensureDefaultAccount($companyId, $userId);
        $normalized = preg_replace('/\s+/', '', $phoneNumber) ?: $phoneNumber;

        $conversation = WhatsappConversation::query()
            ->where('company_id', $companyId)
            ->where('whatsapp_account_id', $account->id)
            ->where('phone_number', $normalized)
            ->first();

        if ($conversation) {
            if ($customerId && ! $conversation->customer_id) {
                $conversation->update(['customer_id' => $customerId]);
            }

            return $conversation;
        }

        $conversation = WhatsappConversation::query()->create([
            'company_id' => $companyId,
            'branch_id' => tenant()->branchId(),
            'whatsapp_account_id' => $account->id,
            'conversation_code' => $this->nextCode($companyId),
            'phone_number' => $normalized,
            'customer_id' => $customerId,
            'lead_id' => $leadId,
            'vendor_id' => $vendorId,
            'status' => WhatsappConversationStatus::Open,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        WhatsappParticipant::query()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'participant_type' => $customerId ? 'customer' : ($leadId ? 'lead' : ($vendorId ? 'vendor' : 'external')),
            'participant_id' => $customerId ?? $leadId ?? $vendorId,
            'phone_number' => $normalized,
            'display_name' => $displayName,
            'role' => 'contact',
        ]);

        return $conversation;
    }

    public function touchActivity(WhatsappConversation $conversation, string $preview, bool $incoming = false): void
    {
        $conversation->update([
            'last_message_preview' => mb_substr($preview, 0, 200),
            'last_activity_at' => now(),
            'unread_count' => $incoming ? $conversation->unread_count + 1 : $conversation->unread_count,
        ]);
    }

    public function markRead(WhatsappConversation $conversation): void
    {
        $conversation->update(['unread_count' => 0]);
    }

    protected function nextCode(int $companyId): string
    {
        $count = WhatsappConversation::query()->where('company_id', $companyId)->count() + 1;

        return 'WA-'.now()->format('ymd').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
