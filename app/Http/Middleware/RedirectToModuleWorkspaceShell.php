<?php

namespace App\Http\Middleware;

use App\Support\Navigation\ModuleShellPresenter;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;
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

        // Only turbo-frame fetches (and lookup create) may render bare embedded content.
        // A full-page load/refresh with ?embedded=1 must restore the module workspace desk.
        if (
            $request->header('Turbo-Frame') === 'module-workspace-content'
            || $request->header('X-Erp-Lookup-Create') === '1'
        ) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $routeName || $this->isDetailRoute($routeName) || $this->isWorkspaceShellRoute($routeName) || $this->isStandaloneAdminRoute($routeName)) {
            return $next($request);
        }

        $user = $request->user();

        // Standalone operator desks (Designer, Sales, Store) render outside the module
        // workspace shell for all users. Production floor stays embedded in the Production
        // workspace for managers — see ProductionOperatorModeTest.
        foreach (OperatorModeRegistry::modes() as $mode) {
            if ($mode->key === OperatorModeKey::Production) {
                continue;
            }

            if ($mode->matchesDeskRoute($routeName)) {
                return $next($request);
            }
        }

        foreach (OperatorModeRegistry::modes() as $mode) {
            if (
                $mode->key === OperatorModeKey::Production
                && $mode->matchesDeskRoute($routeName)
                && (
                    OperatorModeRegistry::enabledFor($user, $mode->key)
                    || $request->boolean('desk')
                )
            ) {
                return $next($request);
            }
        }

        // Artwork list/dashboard routes belong to Commercial for managers — designers must stay on their desk.
        if (
            $user !== null
            && OperatorModeRegistry::enabledFor($user, OperatorModeKey::Designer)
            && OperatorModeRegistry::definition(OperatorModeKey::Designer)->isArtworkFeatureRoute($routeName)
        ) {
            if ($request->hasSession()) {
                $request->session()->reflash();
            }

            return redirect()->to(OperatorModeRegistry::homeUrl(OperatorModeKey::Designer));
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

        $existing = [];

        if (($queryPos = strpos($deskUrl, '?')) !== false) {
            parse_str(substr($deskUrl, $queryPos + 1), $existing);
            $deskUrl = substr($deskUrl, 0, $queryPos);
        }

        $merged = array_merge($existing, $query);

        // Operator modes bounce from multi-tab desks unless ?desk=1 is set.
        // Preserve that escape hatch when we wrap feature index routes into a desk.
        if ($this->operatorNeedsDeskEscapeFlag($request)) {
            $merged['desk'] = 1;
        }

        if ($merged !== []) {
            $deskUrl .= '?'.http_build_query($merged);
        }

        // The feature GET that should display flash is redirected to the desk shell.
        // Reflash so SweetAlert markers survive the extra hop.
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        return redirect()->to($deskUrl);
    }

    /**
     * Operators are redirected away from workspace hubs unless desk=1 is present.
     */
    protected function operatorNeedsDeskEscapeFlag(Request $request): bool
    {
        return OperatorModeRegistry::hasAnyOperatorMode($request->user());
    }

    /**
     * Feature routes that must render as full pages (queues, module dashboards), not desk frames.
     *
     * @return list<string>
     */
    protected function standaloneAdminRoutes(): array
    {
        return [
            'admin.procurement.dashboard',
            'admin.procurement.approvals.index',
        ];
    }

    protected function isStandaloneAdminRoute(string $routeName): bool
    {
        return in_array($routeName, $this->standaloneAdminRoutes(), true);
    }

    protected function isDetailRoute(string $routeName): bool
    {
        foreach (['.create', '.edit', '.show', '.preview', '.compose', '.document', '.receipt', '.pdf', '.export', '.print', '.footer-contact', '.seo-global', '.quick-create'] as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return true;
            }
        }

        // Document wizards such as invoice-from-order and bill-from-PO must not redirect to workspace desks.
        if (preg_match('/\.from-[^.]+$/', $routeName) === 1) {
            return true;
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
