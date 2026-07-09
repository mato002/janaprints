<?php

namespace App\Support\Communications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationPriority;
use App\Enums\NotificationReadStatus;
use App\Enums\NotificationType;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Communications\ErpNotification;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\NotificationPreference;
use App\Models\Communications\NotificationRead;
use App\Models\Communications\SmsCampaign;
use App\Models\Crm\Customer;
use App\Models\Crm\PublicQuoteRequest;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class NotificationService
{
    /**
     * @param  array{
     *     company_id: int,
     *     recipient_user_id: int,
     *     type: NotificationType|string,
     *     title: string,
     *     body: string,
     *     priority?: NotificationPriority|string,
     *     action_url?: string|null,
     *     required_permission?: string|null,
     *     subject_type?: string|null,
     *     subject_id?: int|null,
     *     created_by?: int|null,
     * }  $payload
     */
    public function create(array $payload): ?ErpNotification
    {
        $type = $payload['type'] instanceof NotificationType
            ? $payload['type']
            : NotificationType::from($payload['type']);

        $recipient = User::query()->find($payload['recipient_user_id']);

        if ($recipient === null) {
            return null;
        }

        if (! $this->categoryEnabledForUser($recipient, $payload['company_id'], $type->category())) {
            return null;
        }

        if (! empty($payload['required_permission']) && ! $recipient->can($payload['required_permission'])) {
            return null;
        }

        return DB::transaction(function () use ($payload, $type, $recipient) {
            $priority = $payload['priority'] ?? $type->defaultPriority();
            if (is_string($priority)) {
                $priority = NotificationPriority::from($priority);
            }

            $notification = ErpNotification::query()->create([
                'company_id' => $payload['company_id'],
                'recipient_user_id' => $recipient->id,
                'type' => $type,
                'priority' => $priority,
                'title' => $payload['title'],
                'body' => $payload['body'],
                'action_url' => $payload['action_url'] ?? null,
                'required_permission' => $payload['required_permission'] ?? null,
                'subject_type' => $payload['subject_type'] ?? null,
                'subject_id' => $payload['subject_id'] ?? null,
                'created_by' => $payload['created_by'] ?? null,
            ]);

            NotificationRead::query()->create([
                'notification_id' => $notification->id,
                'user_id' => $recipient->id,
                'status' => NotificationReadStatus::Unread,
            ]);

            $notification = $notification->load(['readState', 'creator', 'recipient']);

            app(CommunicationLogService::class)->recordFromNotification($notification);

            return $notification;
        });
    }

    public function markRead(ErpNotification $notification, User $actor): void
    {
        $this->transition($notification, $actor, NotificationReadStatus::Read, [
            'read_at' => now(),
            'dismissed_at' => null,
        ]);
    }

    public function markUnread(ErpNotification $notification, User $actor): void
    {
        $this->transition($notification, $actor, NotificationReadStatus::Unread, [
            'read_at' => null,
            'dismissed_at' => null,
            'archived_at' => null,
        ]);
    }

    public function dismiss(ErpNotification $notification, User $actor): void
    {
        $this->transition($notification, $actor, NotificationReadStatus::Dismissed, [
            'dismissed_at' => now(),
            'read_at' => $notification->readState?->read_at ?? now(),
        ]);
    }

    public function archive(ErpNotification $notification, User $actor): void
    {
        $this->transition($notification, $actor, NotificationReadStatus::Archived, [
            'archived_at' => now(),
            'read_at' => $notification->readState?->read_at ?? now(),
        ]);
    }

    /**
     * @param  list<int>  $notificationIds
     */
    public function bulkMarkRead(User $actor, array $notificationIds): int
    {
        return $this->bulkTransition($actor, $notificationIds, NotificationReadStatus::Read, [
            'read_at' => now(),
            'dismissed_at' => null,
        ]);
    }

    /**
     * @param  list<int>  $notificationIds
     */
    public function bulkDismiss(User $actor, array $notificationIds): int
    {
        return $this->bulkTransition($actor, $notificationIds, NotificationReadStatus::Dismissed, [
            'dismissed_at' => now(),
            'read_at' => now(),
        ]);
    }

    public function markAllRead(User $actor, ?int $companyId = null): int
    {
        $query = $this->baseQueryForUser($actor, $companyId)
            ->whereHas('readState', fn (Builder $q) => $q->where('status', NotificationReadStatus::Unread));

        $ids = $query->pluck('id')->all();

        return $this->bulkMarkRead($actor, $ids);
    }

    public function unreadCount(User $actor, ?int $companyId = null): int
    {
        return $this->baseQueryForUser($actor, $companyId)
            ->whereHas('readState', fn (Builder $q) => $q->where('status', NotificationReadStatus::Unread))
            ->count();
    }

    public function latestUnreadForUser(User $actor, ?int $companyId = null): ?ErpNotification
    {
        return $this->baseQueryForUser($actor, $companyId)
            ->with(['readState', 'creator'])
            ->whereHas('readState', fn (Builder $q) => $q->where('status', NotificationReadStatus::Unread))
            ->latest()
            ->first();
    }

    /**
     * @return Collection<int, ErpNotification>
     */
    public function recentForUser(User $actor, int $limit = 8, ?int $companyId = null): Collection
    {
        return $this->baseQueryForUser($actor, $companyId)
            ->with(['readState', 'creator', 'subject'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listForUser(User $actor, array $filters = [], bool $adminScope = false): Builder
    {
        $companyId = tenant()->companyId() ?? $actor->company_id;

        $query = ErpNotification::query()
            ->with(['readState', 'creator', 'recipient', 'subject'])
            ->when($companyId, fn (Builder $q) => $q->where('company_id', $companyId));

        if ($adminScope && $actor->can('communications.notifications.admin')) {
            if (! empty($filters['user_id'])) {
                $query->where('recipient_user_id', (int) $filters['user_id']);
            }
        } else {
            $query->where('recipient_user_id', $actor->id);
        }

        $this->applyPermissionFilter($query, $actor);

        if (! empty($filters['status'])) {
            $query->whereHas('readState', fn (Builder $q) => $q->where('status', $filters['status']));
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category'])) {
            $types = collect(NotificationType::cases())
                ->filter(fn (NotificationType $t) => $t->category()->value === $filters['category'])
                ->map(fn (NotificationType $t) => $t->value)
                ->all();
            $query->whereIn('type', $types);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (($filters['view'] ?? null) === 'unread') {
            $query->whereHas('readState', fn (Builder $q) => $q->where('status', NotificationReadStatus::Unread));
        }

        if (($filters['view'] ?? null) === 'critical') {
            $query->where('priority', NotificationPriority::Critical);
        }

        if (($filters['view'] ?? null) === 'archived') {
            $query->whereHas('readState', fn (Builder $q) => $q->where('status', NotificationReadStatus::Archived));
        }

        return $query->latest();
    }

    public function preferencesFor(User $user, int $companyId): NotificationPreference
    {
        return NotificationPreference::query()->firstOrCreate(
            ['user_id' => $user->id, 'company_id' => $companyId],
            [
                'commercial_alerts' => true,
                'production_alerts' => true,
                'accounting_alerts' => true,
                'hr_alerts' => true,
                'system_alerts' => true,
            ],
        );
    }

    /**
     * @param  array<string, bool>  $settings
     */
    public function updatePreferences(User $user, int $companyId, array $settings): NotificationPreference
    {
        $prefs = $this->preferencesFor($user, $companyId);
        $prefs->fill([
            'commercial_alerts' => $settings['commercial_alerts'] ?? $prefs->commercial_alerts,
            'production_alerts' => $settings['production_alerts'] ?? $prefs->production_alerts,
            'accounting_alerts' => $settings['accounting_alerts'] ?? $prefs->accounting_alerts,
            'hr_alerts' => $settings['hr_alerts'] ?? $prefs->hr_alerts,
            'system_alerts' => $settings['system_alerts'] ?? $prefs->system_alerts,
        ]);
        $prefs->save();

        return $prefs;
    }

    public function resolveActionUrl(ErpNotification $notification): ?string
    {
        if (filled($notification->action_url)) {
            return $notification->action_url;
        }

        if (! $notification->subject_type || ! $notification->subject_id) {
            return null;
        }

        if (! $notification->relationLoaded('subject')) {
            $notification->load('subject');
        }

        $subject = $notification->subject;

        if (! $subject instanceof Model) {
            return null;
        }

        return match ($subject::class) {
            Quotation::class => $this->subjectRoute('admin.quotations.show', $subject),
            SalesOrder::class => $this->subjectRoute('admin.sales-orders.show', $subject),
            CustomerInvoice::class => $this->subjectRoute('admin.invoices.show', $subject),
            CustomerPayment::class => $this->subjectRoute('admin.payments.show', $subject),
            DeliveryNote::class => $this->subjectRoute('admin.dispatch.delivery-notes.show', $subject),
            ArtworkRequest::class => $this->subjectRoute('admin.artwork.show', $subject),
            ProductionJobCard::class => $this->subjectRoute('admin.production.job-cards.show', $subject),
            Customer::class => $this->subjectRoute('admin.crm.customers.show', $subject),
            PublicQuoteRequest::class => $this->subjectRoute('admin.public-quote-requests.show', $subject),
            SmsCampaign::class => $this->subjectRoute('admin.communications.sms.campaigns.show', $subject),
            CommunicationConversation::class => Route::has('admin.communications.inbox.index')
                ? route('admin.communications.inbox.index', ['conversation' => $subject->getKey()])
                : null,
            FixedAsset::class => $this->subjectRoute('admin.assets.show', $subject),
            MaintenanceWorkOrder::class => $this->subjectRoute('admin.assets.maintenance.work-orders.show', $subject),
            default => null,
        };
    }

    protected function subjectRoute(string $routeName, Model $subject): ?string
    {
        if (! Route::has($routeName)) {
            return null;
        }

        return route($routeName, $subject);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(ErpNotification $notification): array
    {
        $read = $notification->readState;

        return [
            'id' => $notification->id,
            'type' => $notification->type->value,
            'type_label' => $notification->type->label(),
            'category' => $notification->type->category()->value,
            'category_label' => $notification->type->category()->label(),
            'priority' => $notification->priority->value,
            'priority_label' => $notification->priority->label(),
            'priority_badge' => $notification->priority->badgeClass(),
            'title' => $notification->title,
            'body' => $notification->body,
            'action_url' => $this->resolveActionUrl($notification),
            'status' => $read?->status->value ?? NotificationReadStatus::Unread->value,
            'status_label' => $read?->status->label() ?? NotificationReadStatus::Unread->label(),
            'read_at' => $read?->read_at?->toIso8601String(),
            'dismissed_at' => $read?->dismissed_at?->toIso8601String(),
            'archived_at' => $read?->archived_at?->toIso8601String(),
            'created_by' => $notification->creator?->name,
            'created_at' => $notification->created_at?->toIso8601String(),
            'recipient' => $notification->recipient?->name,
            'is_unread' => ($read?->status ?? NotificationReadStatus::Unread) === NotificationReadStatus::Unread,
        ];
    }

    protected function baseQueryForUser(User $actor, ?int $companyId = null): Builder
    {
        $companyId ??= tenant()->companyId() ?? $actor->company_id;

        $query = ErpNotification::query()
            ->where('recipient_user_id', $actor->id)
            ->when($companyId, fn (Builder $q) => $q->where('company_id', $companyId));

        $this->applyPermissionFilter($query, $actor);

        return $query;
    }

    protected function applyPermissionFilter(Builder $query, User $actor): void
    {
        $query->where(function (Builder $q) use ($actor) {
            $q->whereNull('required_permission');

            if ($actor->hasRole('Super Admin')) {
                return;
            }

            $permissions = $actor->getAllPermissions()->pluck('name');

            foreach ($permissions as $permission) {
                $q->orWhere('required_permission', $permission);
            }
        });
    }

    protected function categoryEnabledForUser(User $user, int $companyId, NotificationCategory $category): bool
    {
        $prefs = $this->preferencesFor($user, $companyId);
        $key = $category->preferenceKey();

        return (bool) $prefs->{$key};
    }

    protected function transition(
        ErpNotification $notification,
        User $actor,
        NotificationReadStatus $status,
        array $timestamps,
    ): void {
        $read = $notification->readState;

        if ($read === null || $read->user_id !== $actor->id) {
            return;
        }

        $read->fill(array_merge(['status' => $status], $timestamps));
        $read->save();
    }

    /**
     * @param  list<int>  $notificationIds
     */
    protected function bulkTransition(User $actor, array $notificationIds, NotificationReadStatus $status, array $timestamps): int
    {
        if ($notificationIds === []) {
            return 0;
        }

        return NotificationRead::query()
            ->where('user_id', $actor->id)
            ->whereIn('notification_id', $notificationIds)
            ->whereHas('notification', fn (Builder $q) => $q->where('recipient_user_id', $actor->id))
            ->update(array_merge(['status' => $status->value], $timestamps));
    }
}
