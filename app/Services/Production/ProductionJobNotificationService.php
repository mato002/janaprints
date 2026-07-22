<?php

namespace App\Services\Production;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Communications\ErpNotification;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\Communications\NotificationService;

class ProductionJobNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function notifyJobQueued(ProductionJobCard $jobCard): void
    {
        $jobCard->loadMissing(['salesOrder.customer', 'company']);

        $customer = $jobCard->salesOrder?->customer;
        $product = $jobCard->salesOrder?->inventoryItem?->item_name
            ?? $jobCard->product_description
            ?? '';

        $body = $customer
            ? __(':customer — :product', ['customer' => $customer->name, 'product' => $product])
            : $product;

        $actionUrl = route('admin.production.floor');

        $payload = [
            'type' => NotificationType::JobCardCreated,
            'title' => __('New job: :number', ['number' => $jobCard->job_card_number]),
            'body' => $body,
            'priority' => NotificationPriority::High,
            'action_url' => $actionUrl,
            'subject_type' => ProductionJobCard::class,
            'subject_id' => $jobCard->id,
            'required_permission' => 'production.view',
        ];

        foreach ($this->eligibleRecipients($jobCard) as $user) {
            if ($this->alreadyNotified($user, $jobCard)) {
                continue;
            }

            $this->notifications->create(array_merge($payload, [
                'company_id' => $user->company_id,
                'recipient_user_id' => $user->id,
            ]));
        }
    }

    protected function alreadyNotified(User $user, ProductionJobCard $jobCard): bool
    {
        return ErpNotification::query()
            ->where('recipient_user_id', $user->id)
            ->where('type', NotificationType::JobCardCreated)
            ->where('subject_type', ProductionJobCard::class)
            ->where('subject_id', $jobCard->id)
            ->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function eligibleRecipients(ProductionJobCard $jobCard)
    {
        return User::query()
            ->where('is_active', true)
            ->where('company_id', $jobCard->company_id)
            ->where(function ($query) {
                $query->whereHas('permissions', fn ($q) => $q->where('name', 'production.view'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'production.view'));
            })
            ->get();
    }
}
