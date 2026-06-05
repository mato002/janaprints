<?php

namespace App\Policies;

use App\Models\Integrations\IntegrationApiKey;
use App\Models\User;
use App\Policies\Concerns\ChecksIntegrationTenant;

class IntegrationApiKeyPolicy
{
    use ChecksIntegrationTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view') || $user->can('integrations.api.manage');
    }

    public function view(User $user, IntegrationApiKey $apiKey): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $apiKey);
    }

    public function create(User $user): bool
    {
        return $user->can('integrations.api.manage') || $user->can('integrations.manage');
    }

    public function update(User $user, IntegrationApiKey $apiKey): bool
    {
        return $this->create($user) && $this->sameTenant($user, $apiKey);
    }

    public function delete(User $user, IntegrationApiKey $apiKey): bool
    {
        return $this->update($user, $apiKey);
    }
}
