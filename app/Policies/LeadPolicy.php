<?php

namespace App\Policies;

use App\Models\Crm\Lead;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Support\Crm\LeadOperationalGuard;

class LeadPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('crm.leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('crm.leads.view') && $this->sameTenant($user, $lead);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('crm.leads.edit') && $this->sameTenant($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('crm.leads.delete')
            && $this->sameTenant($user, $lead)
            && ! app(LeadOperationalGuard::class)->hasDownstreamLinks($lead);
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('crm.customers.create')
            && $user->can('crm.leads.edit')
            && $this->sameTenant($user, $lead);
    }
}
