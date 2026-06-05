<?php

namespace App\Policies;

use App\Models\MasterDataValue;
use App\Models\User;

class MasterDataPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('configuration.master_data.view');
    }

    public function view(User $user, MasterDataValue $value): bool
    {
        return $user->can('configuration.master_data.view');
    }

    public function create(User $user): bool
    {
        return $user->can('configuration.master_data.create');
    }

    public function update(User $user, MasterDataValue $value): bool
    {
        return $user->can('configuration.master_data.edit');
    }

    public function deactivate(User $user, MasterDataValue $value): bool
    {
        return $user->can('configuration.master_data.deactivate');
    }

    public function import(User $user): bool
    {
        return $user->can('configuration.master_data.import');
    }

    public function export(User $user): bool
    {
        return $user->can('configuration.master_data.export');
    }
}
