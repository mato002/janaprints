<?php

namespace App\Policies;

use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class QualityCheckPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.view') && $this->sameTenant($user, $jobCard);
    }

    public function create(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.qc')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === \App\Enums\ProductionJobCardStatus::QualityCheck;
    }

    public function view(User $user, QualityCheck $check): bool
    {
        return $user->can('production.view') && $this->sameTenant($user, $check->jobCard);
    }
}
