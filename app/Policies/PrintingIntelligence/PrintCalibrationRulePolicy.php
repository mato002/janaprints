<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\User;

class PrintCalibrationRulePolicy
{
    use EnsuresCompanyTenant;

    public function view(User $user, PrintCalibrationRule $rule): bool
    {
        return $this->sameCompany($user, $rule);
    }

    public function update(User $user, PrintCalibrationRule $rule): bool
    {
        return $this->sameCompany($user, $rule);
    }
}
