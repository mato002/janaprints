<?php

namespace App\Support\Commercial\Reports;

/**
 * Shared default reporting window for commercial operational reports.
 *
 * Current-month-only defaults hide recent history when activity spans prior months.
 */
class CommercialReportDateDefaults
{
    public static function defaultFromDate(): string
    {
        return now()->subMonths(3)->startOfMonth()->toDateString();
    }

    public static function defaultToDate(): string
    {
        return now()->toDateString();
    }
}
