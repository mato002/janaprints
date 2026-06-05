<?php

namespace App\Services\Assets;

use App\Enums\AssetHealthBand;
use App\Enums\AssetWarrantyStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Facades\Route;

class AssetIntelligenceNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
        protected AssetHealthScoreService $health,
        protected AssetReplacementService $replacement,
    ) {}

    public function scanCompany(int $companyId, int $actorId = 0): int
    {
        $count = 0;
        $count += $this->notifyWarrantyExpiring($companyId, $actorId);
        $count += $this->notifyMaintenanceOverdue($companyId, $actorId);
        $count += $this->notifyCriticalHealth($companyId, $actorId);
        $count += $this->notifyReplacementDue($companyId, $actorId);
        $count += $this->notifyUnassigned($companyId, $actorId);
        $count += $this->notifyPendingTransfers($companyId, $actorId);

        return $count;
    }

    protected function notifyWarrantyExpiring(int $companyId, int $actorId): int
    {
        $warranties = AssetWarranty::query()
            ->where('company_id', $companyId)
            ->where('status', AssetWarrantyStatus::Active)
            ->whereBetween('warranty_end', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->with('asset')
            ->get();

        $sent = 0;
        foreach ($warranties as $warranty) {
            $sent += $this->broadcast($companyId, [
                'type' => NotificationType::PeriodClosingReminder,
                'title' => __('Warranty expiring soon'),
                'body' => __(':asset warranty ends :date', [
                    'asset' => $warranty->asset?->asset_name,
                    'date' => $warranty->warranty_end->format('Y-m-d'),
                ]),
                'priority' => NotificationPriority::Normal,
                'action_url' => $this->asset360Url($warranty->asset),
                'subject_type' => FixedAsset::class,
                'subject_id' => $warranty->fixed_asset_id,
                'created_by' => $actorId,
                'required_permission' => 'assets.360.view',
            ]);
        }

        return $sent;
    }

    protected function notifyMaintenanceOverdue(int $companyId, int $actorId): int
    {
        $plans = MaintenancePlan::query()
            ->where('company_id', $companyId)
            ->where('next_due_date', '<', now()->toDateString())
            ->with('asset')
            ->limit(20)
            ->get();

        $sent = 0;
        foreach ($plans as $plan) {
            $sent += $this->broadcast($companyId, [
                'type' => NotificationType::PeriodClosingReminder,
                'title' => __('Maintenance overdue'),
                'body' => __('Preventive maintenance overdue for :asset', ['asset' => $plan->asset?->asset_name]),
                'priority' => NotificationPriority::High,
                'action_url' => $this->asset360Url($plan->asset),
                'subject_type' => FixedAsset::class,
                'subject_id' => $plan->fixed_asset_id,
                'created_by' => $actorId,
                'required_permission' => 'assets.360.view',
            ]);
        }

        return $sent;
    }

    protected function notifyCriticalHealth(int $companyId, int $actorId): int
    {
        $sent = 0;
        FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->with('category')
            ->each(function (FixedAsset $asset) use ($companyId, $actorId, &$sent) {
                $health = $this->health->score($asset);
                if ($health['band'] !== AssetHealthBand::Critical) {
                    return;
                }
                $sent += $this->broadcast($companyId, [
                    'type' => NotificationType::PeriodClosingReminder,
                    'title' => __('Critical asset health'),
                    'body' => __(':asset health score is :score', ['asset' => $asset->asset_name, 'score' => $health['score']]),
                    'priority' => NotificationPriority::High,
                    'action_url' => $this->asset360Url($asset),
                    'subject_type' => FixedAsset::class,
                    'subject_id' => $asset->id,
                    'created_by' => $actorId,
                    'required_permission' => 'assets.health.view',
                ]);
            });

        return $sent;
    }

    protected function notifyReplacementDue(int $companyId, int $actorId): int
    {
        $sent = 0;
        foreach ($this->replacement->candidates($companyId, null, 10) as $row) {
            if ($row['priority'] !== 'high') {
                continue;
            }
            $asset = $row['asset'];
            $sent += $this->broadcast($companyId, [
                'type' => NotificationType::PeriodClosingReminder,
                'title' => __('Replacement recommended'),
                'body' => __(':asset flagged for replacement review', ['asset' => $asset->asset_name]),
                'priority' => NotificationPriority::Normal,
                'action_url' => $this->asset360Url($asset),
                'subject_type' => FixedAsset::class,
                'subject_id' => $asset->id,
                'created_by' => $actorId,
                'required_permission' => 'assets.replacement.view',
            ]);
        }

        return $sent;
    }

    protected function notifyUnassigned(int $companyId, int $actorId): int
    {
        $assets = FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->whereNull('assigned_to_user_id')
            ->whereNull('assigned_to_employee_id')
            ->where('acquisition_cost', '>=', 50000)
            ->limit(10)
            ->get();

        $sent = 0;
        foreach ($assets as $asset) {
            $sent += $this->broadcast($companyId, [
                'type' => NotificationType::BranchAssigned,
                'title' => __('High-value asset unassigned'),
                'body' => __(':asset has no custodian', ['asset' => $asset->asset_name]),
                'priority' => NotificationPriority::Normal,
                'action_url' => $this->asset360Url($asset),
                'subject_type' => FixedAsset::class,
                'subject_id' => $asset->id,
                'created_by' => $actorId,
                'required_permission' => 'assets.custody.view',
            ]);
        }

        return $sent;
    }

    protected function notifyPendingTransfers(int $companyId, int $actorId): int
    {
        $sent = 0;
        AssetBranchTransfer::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['pending_approval', 'pending_acceptance'])
            ->with('asset')
            ->limit(10)
            ->each(function (AssetBranchTransfer $transfer) use ($companyId, $actorId, &$sent) {
                $sent += $this->broadcast($companyId, [
                    'type' => NotificationType::BranchAssigned,
                    'title' => __('Asset transfer pending'),
                    'body' => __('Transfer :no requires action', ['no' => $transfer->transfer_no]),
                    'priority' => NotificationPriority::High,
                    'action_url' => Route::has('admin.assets.custody.transfers.show')
                        ? route('admin.assets.custody.transfers.show', $transfer)
                        : $this->asset360Url($transfer->asset),
                    'subject_type' => AssetBranchTransfer::class,
                    'subject_id' => $transfer->id,
                    'created_by' => $actorId,
                    'required_permission' => 'assets.custody.manage',
                ]);
            });

        return $sent;
    }

    /** @param  array<string, mixed>  $payload */
    protected function broadcast(int $companyId, array $payload): int
    {
        $users = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $u) => $u->can($payload['required_permission'] ?? 'assets.360.view'));

        $sent = 0;
        foreach ($users as $user) {
            $this->notifications->create([
                ...$payload,
                'company_id' => $companyId,
                'recipient_user_id' => $user->id,
            ]);
            $sent++;
        }

        return $sent;
    }

    protected function asset360Url(?FixedAsset $asset): ?string
    {
        if (! $asset || ! Route::has('admin.assets.360.show')) {
            return null;
        }

        return route('admin.assets.360.show', $asset);
    }
}
