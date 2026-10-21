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
     * True only for turbo-frame fetches that should return the bare workspace fragment.
     * Full-page navigation with ?embedded=1 must keep the main admin shell (CSS/JS).
     */
    public static function rendersEmbeddedFragment(?Request $request = null): bool
    {
        $request ??= request();

        return $request->header('Turbo-Frame') === 'module-workspace-content';
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

        if (str_contains($url, 'embedded=1')) {
            return $url;
        }

        return str_contains($url, '?')
            ? $url.'&embedded=1'
            : $url.'?embedded=1';
    }

    /**
     * URL suitable for erp-main / full-shell navigation (never keeps embedded=1).
     */
    public static function mainUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
            unset($query['embedded']);
        }

        $rebuilt = ($parts['path'] ?? '');
        if ($query !== []) {
            $rebuilt .= '?'.http_build_query($query);
        }
        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        // Preserve absolute URLs when provided.
        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $port = isset($parts['port']) ? ':'.$parts['port'] : '';

            return $parts['scheme'].'://'.$parts['host'].$port.$rebuilt;
        }

        return $rebuilt !== '' ? $rebuilt : $url;
    }

    /**
     * Frame target for links/forms rendered in the current response.
     *
     * Only the nested content fragment should target module-workspace-content.
     * Full-page responses (including ?embedded=1 without a Turbo-Frame header)
     * live inside erp-main — targeting a missing nested frame breaks navigation.
     */
    public static function turboFrame(?Request $request = null): string
    {
        return self::rendersEmbeddedFragment($request)
            ? 'module-workspace-content'
            : 'erp-main';
    }

    /**
     * Breadcrumbs and shell chrome always navigate the outer Drive frame.
     *
     * @return array<string, string>
     */
    public static function mainShellLinkAttributes(): array
    {
        return [
            'data-turbo-frame' => 'erp-main',
            'data-turbo-action' => 'advance',
        ];
    }

    /**
     * Attributes for in-module list/index hops that should stay in the active frame.
     *
     * @return array<string, string>
     */
    public static function turboLinkAttributes(?Request $request = null): array
    {
        return [
            'data-turbo-frame' => self::turboFrame($request),
            'data-turbo-action' => 'advance',
        ];
    }

    /**
     * Attributes for entity detail / create / edit surfaces that load into erp-main.
     * Prefer this over data-leave-workspace (which disables Turbo for a full document visit).
     *
     * @return array<string, string>
     */
    public static function leaveWorkspaceLinkAttributes(): array
    {
        return [
            'data-turbo-frame' => 'erp-main',
            'data-turbo-action' => 'advance',
        ];
    }

    /**
     * Attributes for GET/POST forms that should refresh the active workspace frame.
     *
     * @return array<string, string>
     */
    public static function turboFormAttributes(?Request $request = null): array
    {
        return [
            'data-turbo-frame' => self::turboFrame($request),
        ];
    }

    /**
     * Attributes for forms on detail surfaces that live inside erp-main.
     *
     * @return array<string, string>
     */
    public static function mainFormAttributes(): array
    {
        return [
            'data-turbo-frame' => 'erp-main',
        ];
    }

    /**
     * Full-page visits of nested workspace screens bounce back into the module desk.
     *
     * @param  array<string, mixed>  $catalogParams
     */
    public static function redirectStandaloneToDesk(
        Request $request,
        string $catalogRouteName,
        array $catalogParams = [],
        array $deskQuery = [],
    ): ?\Illuminate\Http\RedirectResponse {
        if (self::rendersEmbeddedFragment($request) || $request->expectsJson()) {
            return null;
        }

        $deskUrl = app(\App\Support\Navigation\ModuleShellPresenter::class)
            ->deskUrlForFeatureRoute($catalogRouteName, $catalogParams);

        if ($deskUrl === null) {
            return null;
        }

        foreach ($deskQuery as $key => $value) {
            if (is_scalar($value) && $value !== '') {
                $deskUrl = self::appendQueryParam($deskUrl, (string) $key, (string) $value);
            }
        }

        $query = $request->query();
        unset($query['embedded'], $query['content']);

        $path = '/'.$request->path();

        if (! str_starts_with($path, '/admin')) {
            return null;
        }

        $contentPath = $query === [] ? $path : $path.'?'.http_build_query($query);

        return redirect()->to(self::appendQueryParam($deskUrl, 'content', $contentPath));
    }

    public static function appendQueryParam(string $url, string $key, string $value): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query[$key] = $value;

        $rebuilt = ($parts['path'] ?? '');
        if ($query !== []) {
            $rebuilt .= '?'.http_build_query($query);
        }

        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $port = isset($parts['port']) ? ':'.$parts['port'] : '';

            return $parts['scheme'].'://'.$parts['host'].$port.$rebuilt;
        }

        return $rebuilt !== '' ? $rebuilt : $url;
    }

    public static function sanitizeDeskContentPath(?string $content): ?string
    {
        if (! is_string($content) || $content === '') {
            return null;
        }

        if (! str_starts_with($content, '/admin/') || str_contains($content, '://') || str_contains($content, '..')) {
            return null;
        }

        if (str_contains($content, 'embedded=1')) {
            return $content;
        }

        return str_contains($content, '?')
            ? $content.'&embedded=1'
            : $content.'?embedded=1';
    }
}
