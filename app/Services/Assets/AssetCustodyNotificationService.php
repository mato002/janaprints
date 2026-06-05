<?php

namespace App\Services\Assets;

use App\Enums\AssetReturnCondition;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Support\Facades\Route;

class AssetCustodyNotificationService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function notifyNewAssignment(FixedAsset $asset, int $actorId): void
    {
        $this->notifyCustodyManagers($asset->company_id, [
            'type' => NotificationType::BranchAssigned,
            'title' => __('Asset assigned'),
            'body' => __(':asset has been assigned.', ['asset' => $asset->asset_name]),
            'priority' => NotificationPriority::Normal,
            'action_url' => Route::has('admin.assets.show') ? route('admin.assets.show', $asset) : null,
            'subject_type' => FixedAsset::class,
            'subject_id' => $asset->id,
            'created_by' => $actorId,
            'required_permission' => 'assets.custody.view',
        ]);
    }

    public function notifyTransferRequest(AssetHandover|AssetBranchTransfer $subject, int $actorId): void
    {
        $asset = $subject->asset;
        $no = $subject instanceof AssetHandover ? $subject->handover_no : $subject->transfer_no;

        $this->notifyCustodyManagers($asset->company_id, [
            'type' => NotificationType::BranchAssigned,
            'title' => __('Asset transfer request'),
            'body' => __('Transfer :no for :asset requires action.', ['no' => $no, 'asset' => $asset->asset_name]),
            'priority' => NotificationPriority::High,
            'action_url' => $this->transferUrl($subject),
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'created_by' => $actorId,
            'required_permission' => 'assets.custody.manage',
        ]);
    }

    public function notifyTransferAccepted(AssetHandover|AssetBranchTransfer $subject, int $actorId): void
    {
        $asset = $subject->asset;
        $no = $subject instanceof AssetHandover ? $subject->handover_no : $subject->transfer_no;

        $this->notifyCustodyManagers($asset->company_id, [
            'type' => NotificationType::BranchAssigned,
            'title' => __('Asset transfer accepted'),
            'body' => __('Transfer :no for :asset was accepted.', ['no' => $no, 'asset' => $asset->asset_name]),
            'priority' => NotificationPriority::Normal,
            'action_url' => $this->transferUrl($subject),
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'created_by' => $actorId,
            'required_permission' => 'assets.custody.view',
        ]);
    }

    public function notifyOverdueReturn(FixedAsset $asset, int $actorId): void
    {
        $this->notifyCustodyManagers($asset->company_id, [
            'type' => NotificationType::PeriodClosingReminder,
            'title' => __('Overdue asset return'),
            'body' => __(':asset is overdue for return.', ['asset' => $asset->asset_name]),
            'priority' => NotificationPriority::High,
            'action_url' => Route::has('admin.assets.show') ? route('admin.assets.show', $asset) : null,
            'subject_type' => FixedAsset::class,
            'subject_id' => $asset->id,
            'created_by' => $actorId,
            'required_permission' => 'assets.custody.view',
        ]);
    }

    public function notifyAssetConditionAlert(FixedAsset $asset, AssetReturnCondition $condition, int $actorId): void
    {
        $this->notifyCustodyManagers($asset->company_id, [
            'type' => NotificationType::ProductionDelayed,
            'title' => $condition === AssetReturnCondition::Lost ? __('Lost asset reported') : __('Damaged asset reported'),
            'body' => __(':asset reported as :condition.', [
                'asset' => $asset->asset_name,
                'condition' => $condition->label(),
            ]),
            'priority' => NotificationPriority::Critical,
            'action_url' => Route::has('admin.assets.show') ? route('admin.assets.show', $asset) : null,
            'subject_type' => FixedAsset::class,
            'subject_id' => $asset->id,
            'created_by' => $actorId,
            'required_permission' => 'assets.custody.manage',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function notifyCustodyManagers(int $companyId, array $payload): void
    {
        $permission = $payload['required_permission'];
        unset($payload['required_permission']);

        $users = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) use ($permission) {
                $q->whereHas('permissions', fn ($p) => $p->where('name', $permission))
                    ->orWhereHas('roles.permissions', fn ($p) => $p->where('name', $permission));
            })
            ->get();

        foreach ($users as $user) {
            $this->notifications->create(array_merge([
                'company_id' => $companyId,
                'recipient_user_id' => $user->id,
            ], $payload));
        }
    }

    protected function transferUrl(AssetHandover|AssetBranchTransfer $subject): ?string
    {
        if ($subject instanceof AssetHandover && Route::has('admin.assets.custody.handovers.show')) {
            return route('admin.assets.custody.handovers.show', $subject);
        }

        if ($subject instanceof AssetBranchTransfer && Route::has('admin.assets.custody.transfers.show')) {
            return route('admin.assets.custody.transfers.show', $subject);
        }

        return null;
    }
}
