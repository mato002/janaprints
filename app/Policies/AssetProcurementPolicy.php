<?php

namespace App\Policies;

use App\Models\User;

class AssetProcurementPolicy
{
    public function viewAssetPurchases(User $user): bool
    {
        return $user->can('assets.acquisition.view') || $user->can('procurement.orders.view');
    }

    public function viewCapitalizationAlerts(User $user): bool
    {
        return $user->can('assets.acquisition.view') || $user->can('assets.capitalize');
    }
}
