<?php

namespace App\Services\Communications\Inbox;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReadStatus;
use App\Enums\NotificationType;
use App\Models\Communications\ErpNotification;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class InboxIncomingNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function notify(
        CommunicationConversation $conversation,
        CommunicationConversationMessage $message,
    ): void {
        if (! Route::has('admin.communications.inbox.index')) {
            return;
        }

        $preview = mb_substr((string) $message->body, 0, 160);
        $customerName = $conversation->display_name ?: __('Customer');

        $payload = [
            'type' => NotificationType::InboxCustomerMessage,
            'title' => __('New client message'),
            'body' => __(':customer: :preview', [
                'customer' => $customerName,
                'preview' => $preview !== '' ? $preview : __('Sent an attachment'),
            ]),
            'priority' => NotificationPriority::High,
            'action_url' => route('admin.communications.inbox.index', [
                'conversation' => $conversation->id,
                'embedded' => 1,
            ]),
            'subject_type' => CommunicationConversation::class,
            'subject_id' => $conversation->id,
            'required_permission' => 'communications.inbox.view',
        ];

        foreach ($this->recipients($conversation) as $user) {
            $this->notifications->create(array_merge($payload, [
                'company_id' => (int) $conversation->company_id,
                'recipient_user_id' => $user->id,
            ]));
        }
    }

    public function markRelatedRead(User $user, CommunicationConversation $conversation): void
    {
        if (! $user->can('communications.notifications.view')) {
            return;
        }

        ErpNotification::query()
            ->where('recipient_user_id', $user->id)
            ->where('subject_type', CommunicationConversation::class)
            ->where('subject_id', $conversation->id)
            ->where('type', NotificationType::InboxCustomerMessage)
            ->whereHas('readState', fn ($query) => $query->where('status', NotificationReadStatus::Unread))
            ->each(fn (ErpNotification $notification) => $this->notifications->markRead($notification, $user));
    }

    /**
     * @return Collection<int, User>
     */
    protected function recipients(CommunicationConversation $conversation): Collection
    {
        $ids = collect([
            $conversation->assigned_user_id,
            $conversation->owner_user_id,
        ])
            ->merge($conversation->watcher_user_ids ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return $this->eligibleRecipients($conversation);
        }

        return User::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    protected function eligibleRecipients(CommunicationConversation $conversation): Collection
    {
        return User::query()
            ->where('company_id', $conversation->company_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereHas('permissions', fn ($q) => $q->where('name', 'communications.inbox.view'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'communications.inbox.view'));
            })
            ->get();
    }
}
