<?php

namespace App\Support\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDisposal;
use App\Models\Assets\AssetMaintenance;
use App\Models\Assets\AssetTransfer;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetLifecycleService
{
    public static function transfer(FixedAsset $asset, array $data, int $userId): AssetTransfer
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Disposed assets cannot be transferred.'),
            ]);
        }

        return DB::transaction(function () use ($asset, $data, $userId) {
            $transfer = AssetTransfer::query()->create([
                'fixed_asset_id' => $asset->id,
                'from_branch_id' => $asset->branch_id,
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'from_user_id' => $asset->assigned_to_user_id,
                'to_user_id' => $data['to_user_id'] ?? null,
                'transfer_date' => $data['transfer_date'],
                'notes' => $data['notes'] ?? null,
                'transferred_by' => $userId,
            ]);

            $asset->update([
                'branch_id' => $data['to_branch_id'] ?? $asset->branch_id,
                'assigned_to_branch_id' => $data['to_branch_id'] ?? $asset->assigned_to_branch_id,
                'assigned_to_user_id' => $data['to_user_id'] ?? $asset->assigned_to_user_id,
            ]);

            return $transfer;
        });
    }

    public static function scheduleMaintenance(FixedAsset $asset, array $data): AssetMaintenance
    {
        return AssetMaintenance::query()->create([
            'fixed_asset_id' => $asset->id,
            'maintenance_type' => $data['maintenance_type'] ?? 'scheduled',
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'status' => 'scheduled',
            'cost' => $data['cost'] ?? 0,
            'description' => $data['description'] ?? null,
        ]);
    }

    public static function completeMaintenance(AssetMaintenance $maintenance, array $data): AssetMaintenance
    {
        $maintenance->update([
            'status' => 'completed',
            'completed_date' => $data['completed_date'] ?? now()->toDateString(),
            'cost' => $data['cost'] ?? $maintenance->cost,
            'description' => $data['description'] ?? $maintenance->description,
        ]);

        return $maintenance->fresh();
    }

    public static function startRepair(FixedAsset $asset): FixedAsset
    {
        $asset->update(['status' => FixedAssetStatus::UnderMaintenance]);

        return $asset->fresh();
    }

    public static function completeRepair(FixedAsset $asset): FixedAsset
    {
        $asset->update(['status' => FixedAssetStatus::Active]);

        return $asset->fresh();
    }

    public static function dispose(FixedAsset $asset, array $data, int $userId): AssetDisposal
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Asset is already disposed.'),
            ]);
        }

        return DB::transaction(function () use ($asset, $data, $userId) {
            $nbv = $asset->netBookValue();
            $proceeds = (float) ($data['disposal_proceeds'] ?? 0);
            $gainLoss = round($proceeds - $nbv, 2);

            $disposal = AssetDisposal::query()->create([
                'fixed_asset_id' => $asset->id,
                'disposal_date' => $data['disposal_date'],
                'disposal_proceeds' => $proceeds,
                'gain_loss_amount' => $gainLoss,
                'disposal_method' => $data['disposal_method'] ?? null,
                'notes' => $data['notes'] ?? null,
                'disposed_by' => $userId,
            ]);

            $asset->update(['status' => FixedAssetStatus::Disposed]);

            return $disposal;
        });
    }
}
