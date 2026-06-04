<?php

namespace App\Support\Tax;

use App\Enums\TaxDocumentType;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRule;

class TaxRuleResolver
{
    public function defaultCode(int $companyId, TaxDocumentType $documentType): ?TaxCode
    {
        $rule = TaxRule::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType->value)
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('priority')
            ->with('taxCode.category')
            ->first();

        return $rule?->taxCode;
    }

    public function resolveCodeId(int $companyId, TaxDocumentType $documentType, ?int $taxCodeId): TaxCode
    {
        if ($taxCodeId) {
            return TaxCode::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->with('category')
                ->findOrFail($taxCodeId);
        }

        $default = $this->defaultCode($companyId, $documentType);

        if (! $default) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tax_code_id' => __('No default tax code configured for :type.', [
                    'type' => $documentType->label(),
                ]),
            ]);
        }

        return $default;
    }
}
