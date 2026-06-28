<?php

namespace App\Support\Production;

class MaterialQuantityFormulaService
{
    /**
     * Evaluate a quantity formula against job quantity.
     *
     * Supported patterns:
     * - Empty/null → quantity_per_unit × job_qty
     * - Numeric literal → fixed quantity
     * - JOB_QTY * N or JOB_QTY / N
     */
    public function evaluate(?string $formula, float $jobQuantity, float $quantityPerUnit = 1): float
    {
        if ($formula === null || trim($formula) === '') {
            return round($quantityPerUnit * $jobQuantity, 3);
        }

        $formula = strtoupper(trim($formula));

        if (is_numeric($formula)) {
            return round((float) $formula, 3);
        }

        if (preg_match('/^JOB_QTY\s*\*\s*([\d.]+)$/i', $formula, $matches)) {
            return round($jobQuantity * (float) $matches[1], 3);
        }

        if (preg_match('/^JOB_QTY\s*\/\s*([\d.]+)$/i', $formula, $matches)) {
            $divisor = (float) $matches[1];

            return $divisor > 0 ? round($jobQuantity / $divisor, 3) : 0;
        }

        return round($quantityPerUnit * $jobQuantity, 3);
    }
}
