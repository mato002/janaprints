<?php

namespace App\Support\Production;

class ProductionImpositionCalculator
{
    public static function estimateSheets(float|int|string|null $quantity, ?int $ups, ?int $stored = null): ?int
    {
        if ($stored !== null && (int) $stored > 0) {
            return (int) $stored;
        }

        if ($quantity === null || $quantity === '' || ! $ups || $ups < 1) {
            return $stored !== null ? (int) $stored : null;
        }

        return (int) ceil(((float) $quantity) / $ups);
    }

    public static function displaySheets(float|int|string|null $quantity, ?int $ups, ?int $stored): string
    {
        $sheets = self::estimateSheets($quantity, $ups, $stored);

        if ($sheets === null) {
            return '—';
        }

        return (string) $sheets;
    }
}
