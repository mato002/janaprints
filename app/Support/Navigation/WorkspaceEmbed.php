<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;

class WorkspaceEmbed
{
    public static function isEmbedded(?Request $request = null): bool
    {
        $request ??= request();

        return $request->header('Turbo-Frame') === 'module-workspace-content';
    }

    public static function inWorkspaceContext(?Request $request = null): bool
    {
        $request ??= request();

        return $request->query('embedded') === '1'
            || $request->header('Turbo-Frame') === 'module-workspace-content';
    }

    public static function url(?string $url, ?Request $request = null): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        if (! self::inWorkspaceContext($request)) {
            return $url;
        }

        return str_contains($url, '?')
            ? $url.'&embedded=1'
            : $url.'?embedded=1';
    }

    public static function turboFrame(?Request $request = null): string
    {
        return self::isEmbedded($request)
            ? 'module-workspace-content'
            : 'erp-main';
    }
}
