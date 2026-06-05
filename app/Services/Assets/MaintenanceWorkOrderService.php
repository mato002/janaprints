<?php

namespace App\Services\Assets;

use App\Enums\DocumentType;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceIncident;
use App\Models\Assets\MaintenanceLog;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Assets\MaintenanceWorkOrderStatusHistory;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;

class MaintenanceWorkOrderService
{
    public function __construct(
        protected MaintenanceTimelineService $timeline,
        protected MaintenanceDowntimeService $downtime,
        protected MaintenanceNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, ?int $branchId, int $userId): MaintenanceWorkOrder
    {
        return DB::transaction(function () use ($data, $companyId, $branchId, $userId) {
            $asset = FixedAsset::query()
                ->where('company_id', $companyId)
                ->findOrFail($data['fixed_asset_id']);

            $number = app(NumberGenerator::class)->generate(DocumentType::MaintenanceWorkOrder, $companyId);

            $order = MaintenanceWorkOrder::query()->create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? $asset->branch_id ?? $branchId,
                'fixed_asset_id' => $asset->id,
                'work_order_no' => $number,
                'maintenance_type' => $data['maintenance_type'] ?? MaintenanceType::Corrective->value,
                'priority' => $data['priority'] ?? MaintenancePriority::Normal->value,
                'status' => MaintenanceWorkOrderStatus::Draft,
                'requested_by' => $userId,
                'description' => $data['description'] ?? null,
                'scheduled_for' => $data['scheduled_for'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'maintenance_plan_id' => $data['maintenance_plan_id'] ?? null,
            ]);

            $this->recordStatus($order, null, $order->status->value, $userId);
            $this->timeline->record($asset, 'created', __('Maintenance work order created'), $order->work_order_no, $order, $userId);

            if (($data['create_incident'] ?? false) || $order->maintenance_type === MaintenanceType::Emergency) {
                $this->createIncident($order, $userId, $data['incident_title'] ?? $order->description);
            }

            return $order->fresh(['asset', 'vendor', 'requester']);
        });
    }

    public function open(MaintenanceWorkOrder $order, int $userId): MaintenanceWorkOrder
    {
        return $this->transition($order, MaintenanceWorkOrderStatus::Open, $userId, function ($order) {
            $order->update(['opened_at' => $order->opened_at ?? now()]);
        });
    }

    public function assign(
        MaintenanceWorkOrder $order,
        int $userId,
        ?int $assigneeId = null,
        ?int $technicianId = null,
        ?int $vendorId = null,
    ): MaintenanceWorkOrder {
        return DB::transaction(function () use ($order, $userId, $assigneeId, $technicianId, $vendorId) {
            $order->update([
                'assigned_to' => $assigneeId,
                'assigned_technician_id' => $technicianId,
                'vendor_id' => $vendorId ?? $order->vendor_id,
            ]);

            $order = $this->transition($order, MaintenanceWorkOrderStatus::Assigned, $userId);

            $this->timeline->record(
                $order->asset,
                'assigned',
                __('Work order assigned'),
                null,
                $order,
                $userId,
            );

            if ($vendorId) {
                $this->timeline->record($order->asset, 'vendor_assigned', __('Vendor assigned'), null, $order, $userId);
            }

            return $order;
        });
    }

    public function start(MaintenanceWorkOrder $order, int $userId): MaintenanceWorkOrder
    {
        return DB::transaction(function () use ($order, $userId) {
            $order = $this->transition($order, MaintenanceWorkOrderStatus::InProgress, $userId);
            $this->timeline->record($order->asset, 'started', __('Maintenance started'), null, $order, $userId);

            if ($order->maintenance_type->blocksProduction() || $order->priority->isCritical()) {
                $this->downtime->startForWorkOrder($order, $userId);
            }

            return $order;
        });
    }

    public function complete(MaintenanceWorkOrder $order, int $userId, ?string $findings = null, ?string $resolution = null): MaintenanceWorkOrder
    {
        return DB::transaction(function () use ($order, $userId, $findings, $resolution) {
            $order->update([
                'findings' => $findings ?? $order->findings,
                'resolution' => $resolution ?? $order->resolution,
                'completed_at' => now(),
            ]);

            $order = $this->transition($order, MaintenanceWorkOrderStatus::Completed, $userId);
            $this->downtime->endForWorkOrder($order);
            $this->timeline->record($order->asset, 'completed', __('Maintenance completed'), null, $order, $userId);
            $this->notifications->notifyCompleted($order, $userId);

            MaintenanceLog::query()->create([
                'company_id' => $order->company_id,
                'fixed_asset_id' => $order->fixed_asset_id,
                'maintenance_work_order_id' => $order->id,
                'logged_by' => $userId,
                'log_type' => 'service',
                'title' => __('Work order completed'),
                'notes' => $resolution,
                'logged_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    public function close(MaintenanceWorkOrder $order, int $userId): MaintenanceWorkOrder
    {
        return DB::transaction(function () use ($order, $userId) {
            $order = $this->transition($order, MaintenanceWorkOrderStatus::Closed, $userId);
            $this->timeline->record($order->asset, 'closed', __('Work order closed'), null, $order, $userId);

            return $order;
        });
    }

    public function changeStatus(MaintenanceWorkOrder $order, MaintenanceWorkOrderStatus $status, int $userId, ?string $notes = null): MaintenanceWorkOrder
    {
        return $this->transition($order, $status, $userId, null, $notes);
    }

    protected function transition(
        MaintenanceWorkOrder $order,
        MaintenanceWorkOrderStatus $status,
        int $userId,
        ?callable $before = null,
        ?string $notes = null,
    ): MaintenanceWorkOrder {
        return DB::transaction(function () use ($order, $status, $userId, $before, $notes) {
            $previous = $order->status;
            $before?->__invoke($order);
            $order->update(['status' => $status]);
            $this->recordStatus($order, $previous->value, $status->value, $userId, $notes);

            if ($status === MaintenanceWorkOrderStatus::WaitingParts || $status === MaintenanceWorkOrderStatus::WaitingVendor) {
                $this->timeline->record($order->asset, 'paused', __('Maintenance paused'), $status->label(), $order, $userId);
            }

            return $order->fresh();
        });
    }

    protected function recordStatus(
        MaintenanceWorkOrder $order,
        ?string $from,
        string $to,
        int $userId,
        ?string $notes = null,
    ): void {
        MaintenanceWorkOrderStatusHistory::query()->create([
            'maintenance_work_order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $userId,
            'notes' => $notes,
            'changed_at' => now(),
        ]);
    }

    protected function createIncident(MaintenanceWorkOrder $order, int $userId, ?string $title): MaintenanceIncident
    {
        $incidentNo = 'INC-'.now()->format('Y').'-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);

        return MaintenanceIncident::query()->create([
            'company_id' => $order->company_id,
            'fixed_asset_id' => $order->fixed_asset_id,
            'maintenance_work_order_id' => $order->id,
            'incident_no' => $incidentNo,
            'severity' => $order->priority,
            'title' => $title ?: __('Maintenance incident'),
            'description' => $order->description,
            'reported_at' => now(),
            'reported_by' => $userId,
        ]);
    }
}
