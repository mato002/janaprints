<?php

namespace App\Support\Communications\Inbox;

use App\Enums\InboxAuditEventType;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationAuditEvent;
use Illuminate\Support\Collection;

class InboxAuditService
{
    public function record(
        CommunicationConversation $conversation,
        InboxAuditEventType $type,
        ?int $userId = null,
        ?array $payload = null,
    ): CommunicationConversationAuditEvent {
        return CommunicationConversationAuditEvent::query()->create([
            'communication_conversation_id' => $conversation->id,
            'event_type' => $type,
            'user_id' => $userId,
            'payload' => $payload,
        ]);
    }

    /**
     * @return Collection<int, CommunicationConversationAuditEvent>
     */
    public function forConversation(CommunicationConversation $conversation, int $limit = 50): Collection
    {
        return $conversation->auditEvents()
            ->with('user')
            ->limit($limit)
            ->get();
    }
}
