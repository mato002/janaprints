<?php

namespace App\Support\Commercial;

use Illuminate\Http\Request;

class PosCertificationScopeResolver
{
    public function resolve(Request $request): PosCertificationScope
    {
        $companyId = tenant()->companyId() ?? (int) $request->user()?->company_id;
        $user = $request->user();

        $canViewAllBranches = $user?->can('commercial.pos.sessions.admin') ?? false;

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } elseif (! $canViewAllBranches) {
            $branchId = tenant()->branchId();
        }

        $fromDate = $request->date('from_date') ?? today()->subDays(6);
        $toDate = $request->date('to_date') ?? today();

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return new PosCertificationScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: $fromDate,
            toDate: $toDate,
        );
    }
}
