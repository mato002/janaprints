<?php

namespace App\Policies;

use App\Models\Integrations\IntegrationWebhook;
use App\Models\User;
use App\Policies\Concerns\ChecksIntegrationTenant;

class IntegrationWebhookPolicy
{
    use ChecksIntegrationTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view') || $user->can('integrations.webhooks.manage');
    }

    public function view(User $user, IntegrationWebhook $webhook): bool
    {
        return $this->viewAny($user) && $this->sameTenant($user, $webhook);
    }

    public function create(User $user): bool
    {
        return $user->can('integrations.webhooks.manage') || $user->can('integrations.manage');
    }

    public function update(User $user, IntegrationWebhook $webhook): bool
    {
        return $this->create($user) && $this->sameTenant($user, $webhook);
    }

    public function manage(User $user, IntegrationWebhook $webhook): bool
    {
        return $this->update($user, $webhook);
    }
}
