<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;

class WorkspaceEmbed
{
    public static function isEmbedded(?Request $request = null): bool
    {
        return self::inWorkspaceContext($request);
    }

    public static function inWorkspaceContext(?Request $request = null): bool
    {
        $request ??= request();

        return $request->query('embedded') === '1'
            || $request->input('embedded') === '1'
            || $request->header('Turbo-Frame') === 'module-workspace-content';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public static function queryParams(array $params = [], ?Request $request = null): array
    {
        $request ??= request();

        if (self::inWorkspaceContext($request)) {
            $params['embedded'] = '1';
        }

        return array_filter($params, fn ($value) => $value !== null && $value !== '');
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
        return self::inWorkspaceContext($request)
            ? 'module-workspace-content'
            : 'erp-main';
    }
}
