<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;

trait ResolvesSettingsScope
{
    /**
     * @return array{companyId: int, branchId: int|null}
     */
    protected function resolveSettingsScope(Request $request): array
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            $companyId = (int) ($request->input('company_id') ?: tenant()->companyId() ?: $user->company_id);
            $branchId = $this->resolveSettingsBranchId($request);

            abort_unless(Company::query()->whereKey($companyId)->exists(), 422);

            if ($branchId) {
                abort_unless(
                    Branch::query()->whereKey($branchId)->where('company_id', $companyId)->exists(),
                    422,
                );
            }

            return compact('companyId', 'branchId');
        }

        $companyId = (int) $user->company_id;
        $branchId = $user->hasRole('Branch Manager')
            ? (int) (tenant()->branchId() ?: $user->default_branch_id)
            : $this->resolveSettingsBranchId($request);

        if ($branchId) {
            abort_unless(
                Branch::query()->whereKey($branchId)->where('company_id', $companyId)->exists(),
                422,
            );
        }

        return compact('companyId', 'branchId');
    }

    protected function resolveSettingsBranchId(Request $request): ?int
    {
        if ($request->isMethod('GET')) {
            return $request->has('branch_id')
                ? ($request->filled('branch_id') ? (int) $request->input('branch_id') : null)
                : tenant()->branchId();
        }

        if ($request->has('branch_id')) {
            return $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        }

        return tenant()->branchId();
    }

    protected function companiesForSettingsUser()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return Company::query()->where('is_active', true)->orderBy('name')->get();
        }

        return Company::query()->whereKey(auth()->user()->company_id)->get();
    }

    protected function branchesForSettingsCompany(int $companyId)
    {
        return Branch::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
