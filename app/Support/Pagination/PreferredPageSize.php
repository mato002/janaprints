<?php

namespace App\Support\Pagination;

class PreferredPageSize
{
    public const COOKIE = 'erp_per_page';

    public const SESSION = 'erp_per_page';

    /** @var list<int> */
    public const ALLOWED = [10, 25, 50, 100];

    public static function sanitize(mixed $value): ?int
    {
        $size = (int) $value;

        return in_array($size, self::ALLOWED, true) ? $size : null;
    }

    public static function current(): ?int
    {
        if (app()->runningInConsole() && ! app()->bound('request')) {
            return null;
        }

        $request = request();

        return self::sanitize($request->input('per_page'))
            ?? self::sanitize($request->cookie(self::COOKIE))
            ?? (self::hasSession($request) ? self::sanitize($request->session()->get(self::SESSION)) : null);
    }

    public static function apply(?int $explicit = null): int
    {
        if ($explicit !== null && $explicit > 100) {
            return $explicit;
        }

        return self::current()
            ?? (($explicit !== null && $explicit > 0) ? $explicit : 25);
    }

    public static function remember(int $size): void
    {
        $size = self::sanitize($size);

        if ($size === null) {
            return;
        }

        $request = request();

        if (self::hasSession($request)) {
            $request->session()->put(self::SESSION, $size);
        }

        cookie()->queue(cookie(self::COOKIE, (string) $size, 60 * 24 * 365));
    }

    protected static function hasSession($request): bool
    {
        return $request !== null && method_exists($request, 'hasSession') && $request->hasSession();
    }
}
