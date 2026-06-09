<?php

namespace App\Support\Platform;

class ModalFormRoutes
{
    /**
     * Routes that must stay as full pages (workstations, dashboards, etc.).
     *
     * @var list<string>
     */
    protected const BLOCKED_ROUTE_FRAGMENTS = [
        '.pos.create',
        '.pos.counter',
        'counter-sales',
        '.dashboard',
    ];

    /**
     * URL path fragments that must not open in a modal.
     *
     * @var list<string>
     */
    protected const BLOCKED_PATH_FRAGMENTS = [
        '/commercial/pos/',
        'counter-sales',
        '/dashboard',
        '/login',
        '/logout',
    ];

    public static function supports(?string $route): bool
    {
        if ($route === null || $route === '') {
            return false;
        }

        foreach (self::BLOCKED_ROUTE_FRAGMENTS as $fragment) {
            if (str_contains($route, $fragment)) {
                return false;
            }
        }

        return str_ends_with($route, '.create') || str_ends_with($route, '.edit');
    }

    public static function supportsUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        if ($path === '') {
            return false;
        }

        foreach (self::BLOCKED_PATH_FRAGMENTS as $fragment) {
            if (str_contains($path, strtolower($fragment))) {
                return false;
            }
        }

        return (bool) preg_match('#/(create|edit)(/|$)#', $path);
    }
}
