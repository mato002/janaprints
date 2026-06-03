<?php

namespace App\Policies;

use App\Models\Platform\SettingsGovernance;
use App\Models\User;

class SettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view');
    }

    public function view(User $user, SettingsGovernance $settings): bool
    {
        return $user->can('settings.view');
    }

    public function update(User $user, SettingsGovernance $settingsGovernance): bool
    {
        return $user->can('settings.manage');
    }
}
