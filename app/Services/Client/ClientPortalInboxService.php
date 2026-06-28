<?php

namespace App\Services\Client;

use App\Enums\InboxMessageStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Communications\Inbox\InboxChatFeedService;
use App\Support\Communications\Inbox\InboxClientTimelineService;
use App\Support\Communications\Inbox\InboxConversationService;
use Illuminate\Support\Collection;

class ClientPortalInboxService
{
    public function __construct(
        protected InboxConversationService $conversations,
        protected InboxClientTimelineService $timeline,
        protected InboxChatFeedService $chatFeed,
    ) {}

    public function resolveConversation(Customer $customer, User $portalUser): CommunicationConversation
    {
        return $this->conversations->findOrCreateForCustomer($customer, (int) $portalUser->id);
    }

    public function loadThread(CommunicationConversation $conversation): CommunicationConversation
    {
        return $conversation->loadMissing([
            'threadMessages' => fn ($q) => $q->where('status', '!=', InboxMessageStatus::Archived->value)
                ->orderBy('created_at'),
            'threadMessages.creator',
            'attachments' => fn ($q) => $q->whereNull('archived_at')->orderBy('created_at'),
            'attachments.uploader',
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function feed(CommunicationConversation $conversation): Collection
    {
        $conversation = $this->loadThread($conversation);
        $events = $this->timeline->build($conversation);

        return $this->chatFeed->build($events, $conversation);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $feed
     */
    public function feedFingerprint(Collection $feed): string
    {
        return $this->chatFeed->fingerprint($feed);
    }

    public function renderFeedHtml(Collection $feed): string
    {
        return view('client.communications.partials.chat-messages', [
            'events' => $feed,
        ])->render();
    }

    public function unreadCountForCustomer(Customer $customer): int
    {
        return CommunicationConversationMessage::query()
            ->where('company_id', $customer->company_id)
            ->where('direction', 'outgoing')
            ->whereNull('read_at')
            ->where('status', '!=', InboxMessageStatus::Archived->value)
            ->whereHas('conversation', fn ($q) => $q
                ->where('customer_id', $customer->id)
                ->where('conversation_type', 'customer'))
            ->count();
    }

    public function markReadForClient(CommunicationConversation $conversation): void
    {
        CommunicationConversationMessage::query()
            ->where('communication_conversation_id', $conversation->id)
            ->where('direction', 'outgoing')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function assertClientOwnsConversation(CommunicationConversation $conversation, Customer $customer): void
    {
        abort_unless(
            (int) $conversation->customer_id === (int) $customer->id
            && $conversation->company_id === $customer->company_id,
            404,
        );
    }
}
