<?php

namespace App\Support\Tax;

use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRate;
use Illuminate\Support\Facades\DB;

class TaxCodeManagementService
{
    public function __construct(
        protected TaxAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCode(int $companyId, array $data, ?int $userId = null): TaxCode
    {
        return DB::transaction(function () use ($companyId, $data, $userId) {
            $code = TaxCode::query()->create([
                'company_id' => $companyId,
                'tax_category_id' => $data['tax_category_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            if (isset($data['rate_percent'], $data['effective_from'])) {
                $this->addRate($code, (float) $data['rate_percent'], $data['effective_from'], $userId);
            }

            $this->audit->log($companyId, $userId, 'tax_code.created', $code, null, $code->only(['code', 'name']));

            return $code->load(['category', 'rates']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCode(TaxCode $taxCode, array $data, ?int $userId = null): TaxCode
    {
        $old = $taxCode->only(['code', 'name', 'description', 'is_active', 'tax_category_id']);

        $taxCode->update([
            'tax_category_id' => $data['tax_category_id'] ?? $taxCode->tax_category_id,
            'code' => $data['code'] ?? $taxCode->code,
            'name' => $data['name'] ?? $taxCode->name,
            'description' => $data['description'] ?? $taxCode->description,
            'is_active' => $data['is_active'] ?? $taxCode->is_active,
            'sort_order' => $data['sort_order'] ?? $taxCode->sort_order,
        ]);

        $this->audit->log(
            $taxCode->company_id,
            $userId,
            'tax_code.updated',
            $taxCode,
            $old,
            $taxCode->only(['code', 'name', 'description', 'is_active', 'tax_category_id']),
        );

        return $taxCode->fresh(['category', 'rates']);
    }

    public function addRate(TaxCode $taxCode, float $ratePercent, string $effectiveFrom, ?int $userId = null): TaxRate
    {
        $rate = TaxRate::query()->create([
            'tax_code_id' => $taxCode->id,
            'rate_percent' => $ratePercent,
            'effective_from' => $effectiveFrom,
            'is_active' => true,
        ]);

        $this->audit->log(
            $taxCode->company_id,
            $userId,
            'tax_rate.created',
            $rate,
            null,
            ['tax_code' => $taxCode->code, 'rate_percent' => $ratePercent, 'effective_from' => $effectiveFrom],
        );

        return $rate;
    }
}
