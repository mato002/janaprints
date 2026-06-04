<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailAccountStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Models\Communications\EmailAccount;
use Illuminate\Support\Collection;

class EmailAccountService
{
    public function defaultForCompany(int $companyId, ?int $branchId = null): ?EmailAccount
    {
        $query = EmailAccount::query()
            ->where('company_id', $companyId)
            ->where('status', EmailAccountStatus::Active);

        if ($branchId) {
            $branchAccount = (clone $query)->where('branch_id', $branchId)->where('is_default', true)->first();
            if ($branchAccount) {
                return $branchAccount;
            }
        }

        return $query->where('is_default', true)->first()
            ?? $query->orderBy('id')->first();
    }

    /**
     * @return Collection<int, EmailAccount>
     */
    public function listForCompany(int $companyId): Collection
    {
        return EmailAccount::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['branch', 'department'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function ensureDefaultAccount(int $companyId, int $userId): EmailAccount
    {
        $existing = $this->defaultForCompany($companyId);
        if ($existing) {
            return $existing;
        }

        return EmailAccount::query()->create([
            'company_id' => $companyId,
            'branch_id' => tenant()->branchId(),
            'name' => __('Primary email'),
            'from_email' => 'noreply@'.strtolower(preg_replace('/\s+/', '', config('app.name', 'janaprints'))).'.local',
            'from_name' => config('app.name'),
            'provider' => EmailProvider::Unconfigured,
            'status' => EmailAccountStatus::Inactive,
            'verification_status' => EmailVerificationStatus::Pending,
            'is_default' => true,
            'created_by' => $userId,
        ]);
    }
}
