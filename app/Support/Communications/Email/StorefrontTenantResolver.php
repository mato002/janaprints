<?php

namespace App\Support\Communications\Email;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

class StorefrontTenantResolver
{
    /**
     * @return array{0: ?Company, 1: ?Branch}
     */
    public function resolve(): array
    {
        $companyCode = (string) config('leads.crm.default_company_code', 'JANA');
        $branchCode = (string) config('leads.crm.default_branch_code', 'HQ');

        $company = Company::query()->where('code', $companyCode)->where('is_active', true)->first();
        $branch = $company
            ? Branch::query()->where('company_id', $company->id)->where('code', $branchCode)->where('is_active', true)->first()
            : null;

        if (! $branch && $company) {
            $branch = Branch::query()
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->orderByDesc('is_head_office')
                ->first();
        }

        return [$company, $branch];
    }

    public function systemUserId(Company $company): int
    {
        $configured = config('leads.system_user_id');

        if (filled($configured)) {
            return (int) $configured;
        }

        return (int) (User::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id') ?? 1);
    }
}
