<?php

namespace App\Support\Communications\Inbox;

use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationNote;

class InboxNoteService
{
    public function __construct(
        protected InboxAuditService $audit,
        protected InboxConversationService $conversations,
    ) {}

    public function add(
        CommunicationConversation $conversation,
        string $body,
        int $userId,
        array $mentionedUserIds = [],
        array $tags = [],
    ): CommunicationConversationNote {
        $mentionedUserIds = $mentionedUserIds ?: $this->parseMentions($body, $conversation->company_id);
        $tags = $tags ?: $this->parseTags($body);

        $note = CommunicationConversationNote::query()->create([
            'communication_conversation_id' => $conversation->id,
            'body' => $body,
            'created_by' => $userId,
            'mentioned_user_ids' => $mentionedUserIds ?: null,
            'tags' => $tags ?: null,
        ]);

        $this->audit->record($conversation, \App\Enums\InboxAuditEventType::NoteAdded, $userId, [
            'summary' => mb_substr(strip_tags($body), 0, 120),
            'mentioned_user_ids' => $mentionedUserIds,
        ]);

        foreach ($mentionedUserIds as $mentionedId) {
            $this->audit->record($conversation, \App\Enums\InboxAuditEventType::WatcherChanged, $userId, [
                'summary' => __('Mentioned :id (notification pending)', ['id' => $mentionedId]),
            ]);
        }
        $this->conversations->touchActivity($conversation, '[Note] '.mb_substr($body, 0, 80));

        return $note;
    }

    /**
     * @return list<int>
     */
    protected function parseMentions(string $body, int $companyId): array
    {
        preg_match_all('/@([A-Za-z0-9._-]+)/', $body, $matches);
        if (empty($matches[1])) {
            return [];
        }

        return \App\Models\User::query()
            ->where('company_id', $companyId)
            ->whereIn('name', $matches[1])
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<string>
     */
    protected function parseTags(string $body): array
    {
        preg_match_all('/#([a-z0-9_-]+)/i', $body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
