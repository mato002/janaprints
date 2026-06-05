<?php

namespace App\Policies;

use App\Models\Integrations\IntegrationProvider;
use App\Models\User;
use App\Policies\Concerns\ChecksIntegrationTenant;

class IntegrationProviderPolicy
{
    use ChecksIntegrationTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view') || $user->can('integrations.providers.manage');
    }

    public function view(User $user, IntegrationProvider $provider): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $provider);
    }

    public function manage(User $user, IntegrationProvider $provider): bool
    {
        return ($user->can('integrations.providers.manage') || $user->can('integrations.manage'))
            && $this->sameTenant($user, $provider);
    }
}
