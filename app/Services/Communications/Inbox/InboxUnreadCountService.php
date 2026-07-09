<?php

namespace App\Services\Communications\Inbox;

use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class InboxUnreadCountService
{
    public function canView(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->can('communications.inbox.view') ?? false;
    }

    public function totalUnreadMessages(?int $companyId = null): int
    {
        $companyId ??= tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return 0;
        }

        return (int) CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->where('unread_count', '>', 0)
            ->sum('unread_count');
    }

    public function unreadConversationCount(?int $companyId = null): int
    {
        $companyId ??= tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return 0;
        }

        return CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->where('unread_count', '>', 0)
            ->count();
    }

    /**
     * @return array{
     *     total_unread: int,
     *     conversation_count: int,
     *     conversations: list<array{id: int, unread_count: int, preview: string|null, name: string|null}>
     * }
     */
    public function unreadSummary(?int $companyId = null): array
    {
        $companyId ??= tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            return [
                'total_unread' => 0,
                'conversation_count' => 0,
                'conversations' => [],
            ];
        }

        $conversations = CommunicationConversation::query()
            ->where('company_id', $companyId)
            ->where('unread_count', '>', 0)
            ->orderByDesc('last_activity_at')
            ->limit(50)
            ->get(['id', 'unread_count', 'last_message_preview', 'display_name']);

        return [
            'total_unread' => (int) $conversations->sum('unread_count'),
            'conversation_count' => $conversations->count(),
            'conversations' => $conversations->map(fn (CommunicationConversation $conversation) => [
                'id' => $conversation->id,
                'unread_count' => (int) $conversation->unread_count,
                'preview' => $conversation->last_message_preview,
                'name' => $conversation->display_name,
            ])->values()->all(),
        ];
    }

    /**
     * @return array{count: int, route: string|null, visible: bool, label: string}
     */
    public function topbarPayload(): array
    {
        $route = Route::has('admin.communications.inbox.index')
            ? route('admin.communications.inbox.index', ['embedded' => 1, 'view' => 'unread'])
            : null;

        return [
            'count' => $this->unreadConversationCount(),
            'route' => $route,
            'visible' => $this->canView(),
            'label' => __('Client Messages'),
        ];
    }
}
