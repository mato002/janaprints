<?php

namespace App\Support\Security;

use App\Models\Branch;
use App\Models\User;
use App\Models\UserBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserBranchAccessService
{
    public function canAccessAllBranches(User $user): bool
    {
        return $user->hasRole('Super Admin')
            || $user->can('reports.consolidated.view')
            || $user->can('branches.access.all');
    }

    public function canAccessBranch(User $user, int $branchId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $branch = Branch::query()->find($branchId);

        if (! $branch || $branch->company_id !== $user->company_id || ! $branch->is_active) {
            return false;
        }

        if ($this->canAccessAllBranches($user)) {
            return true;
        }

        return $this->activeAssignmentQuery($user)
            ->where('branch_id', $branchId)
            ->exists();
    }

    /**
     * @return list<int>
     */
    public function assignedBranchIds(User $user): array
    {
        return $this->activeAssignmentQuery($user)
            ->pluck('branch_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function accessibleBranches(User $user): Collection
    {
        if ($user->hasRole('Super Admin')) {
            $companyId = tenant()->companyId() ?? $user->company_id;

            if (! $companyId) {
                return collect();
            }

            return Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        if ($this->canAccessAllBranches($user)) {
            return Branch::query()
                ->where('company_id', $user->company_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return Branch::query()
            ->whereIn('id', $this->assignedBranchIds($user))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function primaryBranchId(User $user): ?int
    {
        $primary = $this->activeAssignmentQuery($user)
            ->where('is_primary', true)
            ->value('branch_id');

        if ($primary) {
            return (int) $primary;
        }

        if ($user->default_branch_id) {
            return (int) $user->default_branch_id;
        }

        $first = $this->activeAssignmentQuery($user)->value('branch_id');

        return $first ? (int) $first : null;
    }

    public function resolveSessionBranch(User $user, ?int $sessionBranchId): ?int
    {
        if ($sessionBranchId === null) {
            return $this->canAccessAllBranches($user)
                ? null
                : $this->primaryBranchId($user);
        }

        if ($this->canAccessBranch($user, $sessionBranchId)) {
            return $sessionBranchId;
        }

        return $this->primaryBranchId($user);
    }

    public function assertCanSwitchToBranch(User $user, ?int $branchId): void
    {
        if ($branchId === null) {
            if (! $this->canAccessAllBranches($user)) {
                abort(403, __('You are not authorized to view all branches.'));
            }

            return;
        }

        if (! $this->canAccessBranch($user, $branchId)) {
            abort(403, __('You are not assigned to this branch.'));
        }
    }

    public function ensurePrimaryAssignment(User $user): void
    {
        if (! $user->default_branch_id) {
            return;
        }

        UserBranch::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'branch_id' => $user->default_branch_id,
            ],
            [
                'is_primary' => true,
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  list<int>  $branchIds
     */
    public function syncAssignments(User $user, array $branchIds, int $primaryBranchId): void
    {
        $branchIds = collect($branchIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($branchIds->isEmpty()) {
            throw ValidationException::withMessages([
                'branch_ids' => __('At least one branch assignment is required.'),
            ]);
        }

        if (! $branchIds->contains($primaryBranchId)) {
            throw ValidationException::withMessages([
                'default_branch_id' => __('The default branch must be one of the assigned branches.'),
            ]);
        }

        $validBranchIds = Branch::query()
            ->where('company_id', $user->company_id)
            ->whereIn('id', $branchIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($validBranchIds->count() !== $branchIds->count()) {
            throw ValidationException::withMessages([
                'branch_ids' => __('One or more branches are invalid for this company.'),
            ]);
        }

        DB::transaction(function () use ($user, $branchIds, $primaryBranchId): void {
            UserBranch::query()
                ->where('user_id', $user->id)
                ->whereNotIn('branch_id', $branchIds)
                ->delete();

            foreach ($branchIds as $branchId) {
                UserBranch::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'branch_id' => $branchId,
                    ],
                    [
                        'is_primary' => $branchId === $primaryBranchId,
                        'is_active' => true,
                    ],
                );
            }

            UserBranch::query()
                ->where('user_id', $user->id)
                ->where('branch_id', '!=', $primaryBranchId)
                ->update(['is_primary' => false]);
        });
    }

    protected function activeAssignmentQuery(User $user): Builder
    {
        return UserBranch::query()
            ->where('user_id', $user->id)
            ->where('is_active', true);
    }
}
