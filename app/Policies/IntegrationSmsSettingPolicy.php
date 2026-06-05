<?php

namespace App\Policies;

use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\User;
use App\Policies\Concerns\ChecksIntegrationTenant;

class IntegrationSmsSettingPolicy
{
    use ChecksIntegrationTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view') || $user->can('integrations.sms.manage');
    }

    public function view(User $user, IntegrationSmsSetting $setting): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $setting);
    }

    public function create(User $user): bool
    {
        return $user->can('integrations.sms.manage') || $user->can('integrations.manage');
    }

    public function update(User $user, IntegrationSmsSetting $setting): bool
    {
        return $this->create($user) && $this->sameTenant($user, $setting);
    }

    public function manage(User $user, IntegrationSmsSetting $setting): bool
    {
        return $this->update($user, $setting);
    }
}
