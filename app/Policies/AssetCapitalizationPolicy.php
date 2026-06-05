<?php

namespace App\Policies;

use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetCapitalizationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.acquisition.view');
    }

    public function view(User $user, AssetCapitalizationCandidate $candidate): bool
    {
        return $user->can('assets.acquisition.view') && $this->sameTenant($user, $candidate);
    }

    public function capitalize(User $user, AssetCapitalizationCandidate $candidate): bool
    {
        return $user->can('assets.capitalize') && $this->sameTenant($user, $candidate);
    }

    public function reject(User $user, AssetCapitalizationCandidate $candidate): bool
    {
        return $user->can('assets.capitalize') && $this->sameTenant($user, $candidate);
    }
}
