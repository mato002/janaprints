<?php

namespace App\Support\Commercial;

/**
 * Canonical query views for the consolidated POS Desk.
 */
final class PosDeskViews
{
    public const COUNTER = 'counter';

    public const SALES = 'sales';

    public const SESSIONS = 'sessions';

    public const RETURNS = 'returns';

    public const RECON = 'recon';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::COUNTER, self::SALES, self::SESSIONS, self::RETURNS, self::RECON];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::COUNTER;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function deskQuery(string $view = self::COUNTER, array $query = []): array
    {
        unset($query['view']);

        $params = array_merge($query, ['view' => self::normalize($view)]);

        if (($params['view'] ?? self::COUNTER) === self::COUNTER) {
            unset($params['view']);
        }

        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '',
        );
    }

    public static function deskUrl(string $view = self::COUNTER, array $query = []): string
    {
        return match (self::normalize($view)) {
            self::SALES => route('admin.commercial.pos.index', self::deskQuery(self::SALES, $query)),
            self::SESSIONS => route('admin.commercial.pos.sessions.index', $query),
            self::RETURNS => route('admin.commercial.pos.returns.dashboard', $query),
            self::RECON => route('admin.commercial.pos.reconciliation.index', $query),
            default => route('admin.commercial.pos.counter-sales', self::deskQuery(self::COUNTER, $query)),
        };
    }

    public static function counterUrl(array $query = []): string
    {
        return self::deskUrl(self::COUNTER, $query);
    }
}
