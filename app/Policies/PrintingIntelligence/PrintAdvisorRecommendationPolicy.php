<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;
use App\Models\User;

class PrintAdvisorRecommendationPolicy
{
    use EnsuresCompanyTenant;

    public function view(User $user, PrintAdvisorRecommendation $recommendation): bool
    {
        return $this->sameCompany($user, $recommendation);
    }

    public function update(User $user, PrintAdvisorRecommendation $recommendation): bool
    {
        return $this->sameCompany($user, $recommendation)
            && $user->can('printing.advisor.manage');
    }
}
