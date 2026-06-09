<?php

namespace App\Support\Security;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ConsolidatedViewGovernance
{
    public function __construct(
        protected UserBranchAccessService $branchAccess,
    ) {}

    public function canViewConsolidated(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole('Super Admin') || $user->can('reports.consolidated.view');
    }

    public function resolveReportBranchId(?User $user, Request $request, bool $defaultFromTenant = true): ?int
    {
        $branchId = $this->extractBranchIdFromRequest($request, $defaultFromTenant);

        if ($branchId === null) {
            return $this->canViewConsolidated($user)
                ? null
                : $this->fallbackBranchId($user);
        }

        if ($user && ! $this->branchAccess->canAccessBranch($user, $branchId)) {
            return $this->fallbackBranchId($user);
        }

        return $branchId;
    }

    /**
     * @return Collection<int, Branch>
     */
    public function reportBranchOptions(?User $user, int $companyId): Collection
    {
        if ($this->canViewConsolidated($user)) {
            return Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return $this->branchAccess->accessibleBranches($user)
            ->where('company_id', $companyId)
            ->values();
    }

    public function assertConsolidatedAccess(?User $user): void
    {
        if (! $this->canViewConsolidated($user)) {
            abort(403, __('Consolidated company-wide views require authorization.'));
        }
    }

    protected function extractBranchIdFromRequest(Request $request, bool $defaultFromTenant): ?int
    {
        if ($request->has('branch_id')) {
            return $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        }

        if ($defaultFromTenant) {
            return tenant()->branchId();
        }

        return null;
    }

    protected function fallbackBranchId(?User $user): ?int
    {
        return tenant()->branchId() ?? ($user ? $this->branchAccess->primaryBranchId($user) : null);
    }
}
