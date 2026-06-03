<?php

namespace App\Support;

class QuotationCalculator
{
    /**
     * @param  array<int, array{quantity: float|string, unit_price: float|string, discount: float|string, tax_rate: float|string}>  $items
     * @return array{subtotal: float, tax_amount: float, discount_amount: float, total_amount: float, lines: array<int, array{line_total: float}>}
     */
    public static function calculate(array $items, float $headerDiscount = 0): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $lineDiscountTotal = 0;
        $lines = [];

        foreach ($items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);

            $lineSubtotal = max(0, ($qty * $unitPrice) - $discount);
            $lineTax = $lineSubtotal * ($taxRate / 100);
            $lineTotal = $lineSubtotal + $lineTax;

            $lines[$index] = ['line_total' => round($lineTotal, 2)];
            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $lineDiscountTotal += $discount;
        }

        $discountAmount = $lineDiscountTotal + $headerDiscount;
        $totalAmount = max(0, $subtotal + $taxAmount);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'lines' => $lines,
        ];
    }
}
