<?php

namespace App\Support\Sales;

use App\Support\Security\ConsolidatedViewGovernance;
use App\Support\Security\UserBranchAccessService;
use Illuminate\Database\Eloquent\Builder;

class ReceivablesBranchScope
{
    public function __construct(
        protected ConsolidatedViewGovernance $consolidatedView,
        protected UserBranchAccessService $branchAccess,
    ) {}

    /**
     * Resolve the receivables branch filter.
     * Null = company-wide (HQ consolidated). Int = single-branch scope.
     */
    public function resolve(?int $explicitBranchId = null): ?int
    {
        if ($explicitBranchId !== null) {
            return $explicitBranchId;
        }

        $tenantBranch = tenant()->branchId();

        if ($tenantBranch !== null) {
            return $tenantBranch;
        }

        $user = auth()->user();

        if ($user && $this->consolidatedView->canViewConsolidated($user)) {
            return null;
        }

        if ($user) {
            return $this->branchAccess->primaryBranchId($user);
        }

        return null;
    }

    public function apply(Builder $query, ?int $explicitBranchId = null, ?string $column = null): Builder
    {
        $branchId = $this->resolve($explicitBranchId);

        if ($branchId === null) {
            return $query;
        }

        $column ??= $query->getModel()->getTable().'.branch_id';

        return $query->where($column, $branchId);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function mergeFilters(array $filters): array
    {
        if (! array_key_exists('branch_id', $filters)) {
            $filters['branch_id'] = $this->resolve();
        }

        return $filters;
    }
}
