<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Company;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isSuperAdmin = $user?->hasRole('Super Admin') ?? false;

        $company = null;
        $branch = null;

        if ($user) {
            $companyId = session('active_company_id', $user->company_id);
            $branchId = session('active_branch_id', $user->default_branch_id);

            if (! $isSuperAdmin) {
                $companyId = $user->company_id;
                if ($branchId && $user->company_id) {
                    $branchBelongs = Branch::query()
                        ->where('id', $branchId)
                        ->where('company_id', $user->company_id)
                        ->exists();
                    if (! $branchBelongs) {
                        $branchId = $user->default_branch_id;
                    }
                }
            }

            if ($companyId) {
                $company = Company::query()->find($companyId);
            }

            if ($branchId && $company) {
                $branch = Branch::query()
                    ->where('id', $branchId)
                    ->where('company_id', $company->id)
                    ->first();
            }

            if ($isSuperAdmin && ! $company) {
                $company = Company::query()->where('is_active', true)->orderBy('name')->first();
                if ($company && ! $branch) {
                    $branch = $company->branches()->where('is_head_office', true)->first()
                        ?? $company->branches()->first();
                }
            }
        }

        app()->instance(TenantContext::class, new TenantContext(
            company: $company,
            branch: $branch,
            isSuperAdmin: $isSuperAdmin,
        ));

        return $next($request);
    }
}
