<?php

namespace App\Services\Commercial;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Communications\ErpNotification;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Facades\Route;

class PublicQuoteRequestNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function notifyNewRequest(PublicQuoteRequest $quoteRequest): void
    {
        if (! Route::has('admin.public-quote-requests.show')) {
            return;
        }

        $actionUrl = route('admin.public-quote-requests.show', $quoteRequest);
        $quantity = $quoteRequest->quantity ?: __('not specified');

        $payload = [
            'type' => NotificationType::PublicQuoteRequestReceived,
            'title' => __('New Quote Request Received'),
            'body' => __(':customer requested :service for quantity :quantity.', [
                'customer' => $quoteRequest->name,
                'service' => $quoteRequest->service_needed,
                'quantity' => $quantity,
            ]),
            'priority' => NotificationPriority::High,
            'action_url' => $actionUrl,
            'subject_type' => PublicQuoteRequest::class,
            'subject_id' => $quoteRequest->id,
            'required_permission' => 'public_leads.quote_requests.view',
        ];

        foreach ($this->eligibleRecipients() as $user) {
            if ($this->alreadyNotified($user, $quoteRequest)) {
                continue;
            }

            $this->notifications->create(array_merge($payload, [
                'company_id' => $user->company_id,
                'recipient_user_id' => $user->id,
            ]));
        }
    }

    public function markRelatedRead(User $user, PublicQuoteRequest $quoteRequest): void
    {
        if (! $user->can('communications.notifications.manage')) {
            return;
        }

        ErpNotification::query()
            ->where('recipient_user_id', $user->id)
            ->where('subject_type', PublicQuoteRequest::class)
            ->where('subject_id', $quoteRequest->id)
            ->where('type', NotificationType::PublicQuoteRequestReceived)
            ->whereHas('readState', fn ($q) => $q->where('status', \App\Enums\NotificationReadStatus::Unread))
            ->each(fn (ErpNotification $notification) => $this->notifications->markRead($notification, $user));
    }

    protected function alreadyNotified(User $user, PublicQuoteRequest $quoteRequest): bool
    {
        return ErpNotification::query()
            ->where('recipient_user_id', $user->id)
            ->where('type', NotificationType::PublicQuoteRequestReceived)
            ->where('subject_type', PublicQuoteRequest::class)
            ->where('subject_id', $quoteRequest->id)
            ->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function eligibleRecipients()
    {
        return User::query()
            ->where('is_active', true)
            ->whereNotNull('company_id')
            ->where(function ($query) {
                $query->whereHas('permissions', fn ($q) => $q->where('name', 'public_leads.quote_requests.view'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'public_leads.quote_requests.view'));
            })
            ->get();
    }
}
