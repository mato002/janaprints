<?php

namespace App\Policies;

use App\Models\Assets\AssetCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetCategoryPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.categories.view') || $user->can('assets.view');
    }

    public function view(User $user, AssetCategory $category): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $category);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.categories.manage');
    }

    public function update(User $user, AssetCategory $category): bool
    {
        return $user->can('assets.categories.manage') && $this->sameTenant($user, $category);
    }

    public function archive(User $user, AssetCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
