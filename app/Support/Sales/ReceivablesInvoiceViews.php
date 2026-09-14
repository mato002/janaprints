<?php

namespace App\Support\Sales;

/**
 * Nested registers under Accounting → Receivables → Invoices.
 */
final class ReceivablesInvoiceViews
{
    public const INVOICES = 'invoices';

    public const JOBS = 'jobs';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::INVOICES, self::JOBS];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::INVOICES;
    }

    public static function isJobs(?string $view): bool
    {
        return self::normalize($view) === self::JOBS;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function url(string $view = self::INVOICES, array $query = []): string
    {
        $view = self::normalize($view);

        if ($view === self::INVOICES) {
            unset($query['view']);

            return route('admin.invoices.index', $query);
        }

        return route('admin.invoices.index', array_merge($query, ['view' => self::JOBS]));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function invoicesUrl(array $query = []): string
    {
        return self::url(self::INVOICES, $query);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function jobsUrl(array $query = []): string
    {
        return self::url(self::JOBS, $query);
    }
}
