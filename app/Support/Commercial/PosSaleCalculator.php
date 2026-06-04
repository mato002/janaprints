<?php

namespace App\Support\Commercial;

class PosSaleCalculator
{
    /**
     * @param  list<array{quantity: float|int|string, unit_price: float|int|string, discount_amount?: float|int|string, tax_amount?: float|int|string}>  $lines
     * @return array{subtotal: string, discount_amount: string, tax_amount: string, total_amount: string}
     */
    public function totals(array $lines, float|string $saleDiscount = 0, float|string $saleTax = 0): array
    {
        $subtotal = 0.0;
        $lineDiscount = 0.0;
        $lineTax = 0.0;

        foreach ($lines as $line) {
            $qty = (float) $line['quantity'];
            $unit = (float) $line['unit_price'];
            $discount = (float) ($line['discount_amount'] ?? 0);
            $tax = (float) ($line['tax_amount'] ?? 0);
            $lineSubtotal = max(0, ($qty * $unit) - $discount);

            $subtotal += $lineSubtotal;
            $lineDiscount += $discount;
            $lineTax += $tax;
        }

        $saleDiscount = (float) $saleDiscount;
        $saleTax = (float) $saleTax;
        $discountAmount = $lineDiscount + $saleDiscount;
        $taxAmount = $lineTax + $saleTax;
        $total = max(0, $subtotal - $saleDiscount + $taxAmount);

        return [
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'tax_amount' => number_format($taxAmount, 2, '.', ''),
            'total_amount' => number_format($total, 2, '.', ''),
        ];
    }

    public function lineTotal(float|string $quantity, float|string $unitPrice, float|string $discount = 0, float|string $tax = 0): string
    {
        $base = max(0, ((float) $quantity * (float) $unitPrice) - (float) $discount);

        return number_format($base + (float) $tax, 2, '.', '');
    }
}
