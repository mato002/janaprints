<?php

namespace App\Support\Tax;

use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRate;
use Illuminate\Validation\ValidationException;

class TaxRateResolver
{
    public function resolve(TaxCode $taxCode, string $documentDate): float
    {
        $taxCode->loadMissing('category');

        $rate = TaxRate::query()
            ->where('tax_code_id', $taxCode->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $documentDate)
            ->where(function ($q) use ($documentDate) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $documentDate);
            })
            ->orderByDesc('effective_from')
            ->first();

        if (! $rate) {
            throw ValidationException::withMessages([
                'tax_code' => __('No active tax rate for :code on :date.', [
                    'code' => $taxCode->code,
                    'date' => $documentDate,
                ]),
            ]);
        }

        return $taxCode->category->type->effectiveRatePercent((float) $rate->rate_percent);
    }
}
