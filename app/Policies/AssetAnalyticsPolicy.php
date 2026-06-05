<?php

namespace App\Policies;

use App\Models\User;

class AssetAnalyticsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assets.analytics.view');
    }

    public function viewExecutive(User $user): bool
    {
        return $user->can('assets.analytics.view');
    }

    public function viewBranch(User $user): bool
    {
        return $user->can('assets.analytics.view');
    }
}
