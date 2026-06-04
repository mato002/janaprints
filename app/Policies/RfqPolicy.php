<?php

namespace App\Policies;

use App\Models\Procurement\Rfq;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class RfqPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.rfq.view');
    }

    public function view(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.rfq.view') && $this->sameTenant($user, $rfq);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.rfq.create');
    }

    public function update(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.rfq.edit')
            && $this->sameTenant($user, $rfq)
            && $rfq->status->isEditable();
    }

    public function manage(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.rfq.edit') && $this->sameTenant($user, $rfq);
    }

    public function compare(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.comparison.view') && $this->sameTenant($user, $rfq);
    }

    public function award(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.comparison.manage') && $this->sameTenant($user, $rfq);
    }

    public function convert(User $user, Rfq $rfq): bool
    {
        return $user->can('procurement.orders.create')
            && $this->sameTenant($user, $rfq)
            && $rfq->status->canConvert();
    }
}
