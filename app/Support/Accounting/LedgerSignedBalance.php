<?php

namespace App\Support\Accounting;

use App\Enums\NormalBalance;

class LedgerSignedBalance
{
    /**
     * Signed balance for balance-sheet style accounts (assets positive on debit).
     */
    public static function balanceSheetAmount(float $debit, float $credit, NormalBalance $normal): float
    {
        $raw = round($debit - $credit, 2);

        return match ($normal) {
            NormalBalance::Debit => $raw,
            NormalBalance::Credit => -$raw,
        };
    }

    /**
     * P&L period amount (revenue/expense display convention).
     */
    public static function profitAndLossAmount(float $debit, float $credit, NormalBalance $normal): float
    {
        return match ($normal) {
            NormalBalance::Credit => round($credit - $debit, 2),
            NormalBalance::Debit => round($debit - $credit, 2),
        };
    }
}
