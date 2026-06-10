<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintForecastSnapshot;
use App\Models\User;

class PrintForecastSnapshotPolicy
{
    use EnsuresCompanyTenant;

    public function view(User $user, PrintForecastSnapshot $snapshot): bool
    {
        return $this->sameCompany($user, $snapshot);
    }
}
