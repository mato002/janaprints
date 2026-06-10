<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Models\User;

class PrintProfitabilitySnapshotPolicy
{
    use EnsuresCompanyTenant;

    public function view(User $user, PrintProfitabilitySnapshot $snapshot): bool
    {
        return $this->sameCompany($user, $snapshot);
    }
}
