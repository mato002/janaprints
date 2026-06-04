<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Models\Communications\WhatsappAccount;
use Illuminate\Support\Collection;

class WhatsappAccountService
{
    public function defaultForCompany(int $companyId, ?int $branchId = null): ?WhatsappAccount
    {
        $query = WhatsappAccount::query()
            ->where('company_id', $companyId)
            ->where('status', WhatsappAccountStatus::Active);

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
     * @return Collection<int, WhatsappAccount>
     */
    public function listForCompany(int $companyId): Collection
    {
        return WhatsappAccount::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with('branch')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function ensureDefaultAccount(int $companyId, int $userId): WhatsappAccount
    {
        $existing = $this->defaultForCompany($companyId);
        if ($existing) {
            return $existing;
        }

        return WhatsappAccount::query()->create([
            'company_id' => $companyId,
            'branch_id' => tenant()->branchId(),
            'name' => __('Primary WhatsApp'),
            'phone_number' => '+0000000000',
            'display_name' => config('app.name'),
            'provider' => WhatsappProvider::Unconfigured,
            'status' => WhatsappAccountStatus::Inactive,
            'verification_status' => WhatsappVerificationStatus::Pending,
            'is_default' => true,
            'created_by' => $userId,
        ]);
    }
}
