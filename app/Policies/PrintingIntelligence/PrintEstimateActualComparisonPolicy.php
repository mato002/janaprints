<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\User;

class PrintEstimateActualComparisonPolicy
{
    use EnsuresCompanyTenant;

    public function view(User $user, PrintEstimateActualComparison $comparison): bool
    {
        return $this->sameCompany($user, $comparison);
    }
}
