<?php

namespace App\Support\Hr;

use Illuminate\Http\Request;

class HrKpiPresenter
{
    public function __construct(
        protected HrKpiScopeResolver $scopeResolver,
        protected HrWorkforceIntelligenceService $intelligence,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $dashboard = $this->intelligence->dashboard($resolved['scope']);

        return [
            'title' => __('Workforce Intelligence'),
            'description' => __('HR KPI center with dimensional dashboards and workforce rankings.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'departments' => $resolved['departments'],
            'jobTitles' => $resolved['jobTitles'],
            'employees' => $resolved['employees'],
            'employmentStatuses' => $resolved['employmentStatuses'],
            'dimensions' => $resolved['dimensions'],
            'active_dimension' => $resolved['filters']['dimension'],
            'can_export' => $resolved['can_export'],
            'kpis' => $dashboard['kpis'],
            'dimension_rows' => $dashboard['dimension_rows'],
            'rankings' => $dashboard['rankings'],
            'rating_distribution' => $dashboard['rating_distribution'],
        ];
    }
}
