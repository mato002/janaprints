<?php

namespace App\Support\Tax;

use App\Enums\TaxDocumentType;
use App\Models\Tax\TaxCode;

class TaxCalculationService
{
    public function __construct(
        protected TaxRuleResolver $rules,
        protected TaxRateResolver $rates,
    ) {}

    /**
     * @param  array<int, array{
     *     quantity: float|string,
     *     unit_price?: float|string,
     *     unit_cost?: float|string,
     *     discount?: float|string,
     *     tax_code_id?: int|null,
     *     tax_rate?: float|string
     * }>  $items
     * @return array{
     *     subtotal: float,
     *     tax_amount: float,
     *     discount_amount: float,
     *     total_amount: float,
     *     lines: array<int, array{line_subtotal: float, tax_amount: float, line_total: float, tax_code_id: ?int, tax_rate: float}>,
     *     tax_summary: array<int, array{tax_code_id: int, tax_code: string, tax_name: string, tax_category_id: int, tax_rate: float, taxable_amount: float, tax_amount: float}>
     * }
     */
    public function calculate(
        int $companyId,
        TaxDocumentType $documentType,
        array $items,
        string $documentDate,
        float $headerDiscount = 0,
    ): array {
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $lineDiscountTotal = 0.0;
        $lines = [];
        $taxBuckets = [];

        foreach ($items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitAmount = (float) ($item['unit_price'] ?? $item['unit_cost'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);

            $taxCode = $this->rules->resolveCodeId(
                $companyId,
                $documentType,
                ! empty($item['tax_code_id']) ? (int) $item['tax_code_id'] : null,
            );

            $taxRate = isset($item['tax_rate']) && ! isset($item['tax_code_id'])
                ? round((float) $item['tax_rate'], 4)
                : $this->rates->resolve($taxCode, $documentDate);

            $lineSubtotal = max(0, round(($qty * $unitAmount) - $discount, 2));
            $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $lines[$index] = [
                'line_subtotal' => $lineSubtotal,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
                'tax_code_id' => $taxCode->id,
                'tax_rate' => $taxRate,
            ];

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $lineDiscountTotal += $discount;

            $bucketKey = $taxCode->id;
            if (! isset($taxBuckets[$bucketKey])) {
                $taxBuckets[$bucketKey] = [
                    'tax_code_id' => $taxCode->id,
                    'tax_code' => $taxCode->code,
                    'tax_name' => $taxCode->name,
                    'tax_category_id' => $taxCode->tax_category_id,
                    'tax_rate' => $taxRate,
                    'taxable_amount' => 0.0,
                    'tax_amount' => 0.0,
                ];
            }

            $taxBuckets[$bucketKey]['taxable_amount'] += $lineSubtotal;
            $taxBuckets[$bucketKey]['tax_amount'] += $lineTax;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'discount_amount' => round($lineDiscountTotal + $headerDiscount, 2),
            'total_amount' => round(max(0, $subtotal + $taxAmount), 2),
            'lines' => $lines,
            'tax_summary' => array_values(array_map(fn ($bucket) => [
                ...$bucket,
                'taxable_amount' => round($bucket['taxable_amount'], 2),
                'tax_amount' => round($bucket['tax_amount'], 2),
            ], $taxBuckets)),
        ];
    }
}
