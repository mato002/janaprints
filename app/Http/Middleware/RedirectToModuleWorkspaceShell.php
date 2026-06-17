<?php

namespace App\Http\Middleware;

use App\Support\Navigation\ModuleShellPresenter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToModuleWorkspaceShell
{
    public function __construct(
        protected ModuleShellPresenter $shell,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin') && ! $request->is('admin/*')) {
            return $next($request);
        }

        if (! $request->isMethod('GET') || $request->expectsJson()) {
            return $next($request);
        }

        // Allow embedded workspace content (frame fetch or redirect follow after form actions).
        if (
            $request->header('Turbo-Frame') === 'module-workspace-content'
            || $request->query('embedded') === '1'
        ) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $routeName || $this->isDetailRoute($routeName) || $this->isWorkspaceShellRoute($routeName)) {
            return $next($request);
        }

        $deskUrl = $this->shell->deskUrlForFeatureRoute(
            $routeName,
            $this->routeParameters($request),
        );

        if ($deskUrl === null) {
            return $next($request);
        }

        $query = $request->query();

        unset($query['embedded']);

        if ($query !== []) {
            $existing = [];

            if (($queryPos = strpos($deskUrl, '?')) !== false) {
                parse_str(substr($deskUrl, $queryPos + 1), $existing);
                $deskUrl = substr($deskUrl, 0, $queryPos);
            }

            $query = array_diff_key($query, $existing);

            if ($query !== []) {
                $deskUrl .= (str_contains($deskUrl, '?') ? '&' : '?').http_build_query($query);
            }
        }

        return redirect()->to($deskUrl);
    }

    protected function isDetailRoute(string $routeName): bool
    {
        foreach (['.create', '.edit', '.show', '.compose', '.document', '.receipt', '.pdf', '.footer-contact', '.seo-global'] as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return true;
            }
        }

        return false;
    }

    protected function isWorkspaceShellRoute(string $routeName): bool
    {
        if (! str_starts_with($routeName, 'admin.workspaces.')) {
            return false;
        }

        if (str_ends_with($routeName, '.section')) {
            return true;
        }

        return (bool) preg_match('/^admin\.workspaces\.[a-z0-9-]+$/', $routeName);
    }

    /**
     * @return array<string, mixed>
     */
    protected function routeParameters(Request $request): array
    {
        $parameters = [];

        foreach ($request->route()?->parameters() ?? [] as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
