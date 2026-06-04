<?php

namespace App\Support\Sales;

class InvoiceCalculator
{
    /**
     * @param  array<int, array{quantity: float|string, unit_price: float|string, discount?: float|string, tax_rate?: float|string}>  $items
     * @return array{
     *     subtotal: float,
     *     tax_amount: float,
     *     discount_amount: float,
     *     total_amount: float,
     *     lines: array<int, array{line_subtotal: float, tax_amount: float, line_total: float}>,
     *     tax_summary: array<int, array{tax_rate: float, taxable_amount: float, tax_amount: float}>
     * }
     */
    public static function calculate(array $items, float $headerDiscount = 0): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $lineDiscountTotal = 0;
        $lines = [];
        $taxBuckets = [];

        foreach ($items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $taxRate = round((float) ($item['tax_rate'] ?? 0), 2);

            $lineSubtotal = max(0, ($qty * $unitPrice) - $discount);
            $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $lines[$index] = [
                'line_subtotal' => round($lineSubtotal, 2),
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $lineDiscountTotal += $discount;

            if ($taxRate > 0) {
                $taxBuckets[$taxRate] = [
                    'tax_rate' => $taxRate,
                    'taxable_amount' => ($taxBuckets[$taxRate]['taxable_amount'] ?? 0) + $lineSubtotal,
                    'tax_amount' => ($taxBuckets[$taxRate]['tax_amount'] ?? 0) + $lineTax,
                ];
            }
        }

        $discountAmount = $lineDiscountTotal + $headerDiscount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round(max(0, $subtotal + $taxAmount), 2),
            'lines' => $lines,
            'tax_summary' => array_values(array_map(fn ($bucket) => [
                'tax_rate' => $bucket['tax_rate'],
                'taxable_amount' => round($bucket['taxable_amount'], 2),
                'tax_amount' => round($bucket['tax_amount'], 2),
            ], $taxBuckets)),
        ];
    }
}
