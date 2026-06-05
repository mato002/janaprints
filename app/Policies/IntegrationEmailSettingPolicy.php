<?php

namespace App\Policies;

use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\User;
use App\Policies\Concerns\ChecksIntegrationTenant;

class IntegrationEmailSettingPolicy
{
    use ChecksIntegrationTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view') || $user->can('integrations.email.manage');
    }

    public function view(User $user, IntegrationEmailSetting $setting): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $setting);
    }

    public function create(User $user): bool
    {
        return $user->can('integrations.email.manage') || $user->can('integrations.manage');
    }

    public function update(User $user, IntegrationEmailSetting $setting): bool
    {
        return $this->create($user) && $this->sameTenant($user, $setting);
    }

    public function manage(User $user, IntegrationEmailSetting $setting): bool
    {
        return $this->update($user, $setting);
    }
}
