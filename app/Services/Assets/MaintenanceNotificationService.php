<?php

namespace App\Services\Assets;

use App\Enums\MaintenancePriority;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Facades\Route;

class MaintenanceNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function notifyCriticalBreakdown(MaintenanceWorkOrder $order, int $actorId): void
    {
        if (! $order->priority->isCritical()) {
            return;
        }

        $this->notifyMaintenanceManagers($order->company_id, [
            'type' => NotificationType::ProductionDelayed,
            'title' => __('Critical maintenance breakdown'),
            'body' => __(':asset requires immediate attention. Work order :no.', [
                'asset' => $order->asset?->asset_name,
                'no' => $order->work_order_no,
            ]),
            'priority' => NotificationPriority::Critical,
            'action_url' => Route::has('admin.assets.maintenance.work-orders.show')
                ? route('admin.assets.maintenance.work-orders.show', $order)
                : null,
            'subject_type' => MaintenanceWorkOrder::class,
            'subject_id' => $order->id,
            'created_by' => $actorId,
            'required_permission' => 'maintenance.view',
        ]);
    }

    public function notifyOverduePlan(MaintenancePlan $plan): void
    {
        $this->notifyMaintenanceManagers($plan->company_id, [
            'type' => NotificationType::PeriodClosingReminder,
            'title' => __('Overdue preventive maintenance'),
            'body' => __(':plan for :asset is overdue.', [
                'plan' => $plan->plan_name,
                'asset' => $plan->asset?->asset_name,
            ]),
            'priority' => NotificationPriority::High,
            'required_permission' => 'maintenance.view',
            'subject_type' => MaintenancePlan::class,
            'subject_id' => $plan->id,
        ]);
    }

    public function notifyUpcoming(MaintenancePlan $plan): void
    {
        $this->notifyMaintenanceManagers($plan->company_id, [
            'type' => NotificationType::PeriodClosingReminder,
            'title' => __('Upcoming maintenance'),
            'body' => __(':plan for :asset is due on :date.', [
                'plan' => $plan->plan_name,
                'asset' => $plan->asset?->asset_name,
                'date' => $plan->next_due_date?->format('Y-m-d'),
            ]),
            'priority' => NotificationPriority::Normal,
            'required_permission' => 'maintenance.view',
            'subject_type' => MaintenancePlan::class,
            'subject_id' => $plan->id,
        ]);
    }

    public function notifyCompleted(MaintenanceWorkOrder $order, int $actorId): void
    {
        $this->notifyMaintenanceManagers($order->company_id, [
            'type' => NotificationType::ProductionCompleted,
            'title' => __('Maintenance completed'),
            'body' => __('Work order :no for :asset has been completed.', [
                'no' => $order->work_order_no,
                'asset' => $order->asset?->asset_name,
            ]),
            'priority' => NotificationPriority::Normal,
            'action_url' => Route::has('admin.assets.maintenance.work-orders.show')
                ? route('admin.assets.maintenance.work-orders.show', $order)
                : null,
            'subject_type' => MaintenanceWorkOrder::class,
            'subject_id' => $order->id,
            'created_by' => $actorId,
            'required_permission' => 'maintenance.view',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function notifyMaintenanceManagers(int $companyId, array $payload): void
    {
        $users = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('permissions', fn ($p) => $p->where('name', 'maintenance.view'))
                    ->orWhereHas('roles.permissions', fn ($p) => $p->where('name', 'maintenance.view'));
            })
            ->get();

        foreach ($users as $user) {
            $this->notifications->create(array_merge([
                'company_id' => $companyId,
                'recipient_user_id' => $user->id,
            ], $payload));
        }
    }
}
