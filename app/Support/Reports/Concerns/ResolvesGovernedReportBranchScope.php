<?php

namespace App\Support\Reports\Concerns;

use App\Models\User;
use App\Support\Security\ConsolidatedViewGovernance;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ResolvesGovernedReportBranchScope
{
    protected function resolveGovernedBranchId(Request $request, bool $defaultFromTenant = true): ?int
    {
        return app(ConsolidatedViewGovernance::class)->resolveReportBranchId(
            $request->user(),
            $request,
            $defaultFromTenant,
        );
    }

    protected function governedReportBranches(Request $request, int $companyId): Collection
    {
        return app(ConsolidatedViewGovernance::class)->reportBranchOptions(
            $request->user(),
            $companyId,
        );
    }

    protected function canViewConsolidatedReports(?User $user): bool
    {
        return app(ConsolidatedViewGovernance::class)->canViewConsolidated($user);
    }
}
