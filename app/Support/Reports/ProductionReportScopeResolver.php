<?php

namespace App\Support\Reports;

use Illuminate\Http\Request;

class ProductionReportScopeResolver
{
    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
    ) {}

    /**
     * @return array{
     *     scope: IntelligenceScope,
     *     branches: \Illuminate\Support\Collection,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     tab: string
     * }
     */
    public function resolve(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve(
            $request,
            defaultBranchFromTenant: false,
        );

        $tab = (string) $request->query('tab', 'throughput');
        $validTabs = array_keys(config('production_reports.tabs', []));

        if (! in_array($tab, $validTabs, true)) {
            $tab = 'throughput';
        }

        $resolved['tab'] = $tab;
        $resolved['filters']['tab'] = $tab;

        return $resolved;
    }
}
