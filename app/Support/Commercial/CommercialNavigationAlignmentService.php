<?php

namespace App\Support\Commercial;

/**
 * Centralizes Commercial workspace navigation and legacy report alignment.
 */
class CommercialNavigationAlignmentService
{
    /**
     * @return list<array{label: string, description: string, route: string, permission: string|null}>
     */
    public function commercialReportsHubLinks(): array
    {
        return [
            [
                'label' => __('Commercial Intelligence'),
                'description' => __('Profitability, waste, and outsource margin analysis.'),
                'route' => 'admin.reports.commercial-intelligence',
                'permission' => 'intelligence.commercial.view|reports.view',
            ],
            [
                'label' => __('Commercial 360'),
                'description' => __('Sales and customer management intelligence.'),
                'route' => 'admin.reports.commercial360',
                'permission' => 'intelligence.commercial.view|reports.view',
            ],
            [
                'label' => __('Sales Reports'),
                'description' => __('Revenue, orders, and sales performance.'),
                'route' => 'commercial.reports.sales.index',
                'permission' => 'commercial.reports.sales.view',
            ],
            [
                'label' => __('Quotation Reports'),
                'description' => __('Quote pipeline, win rates, and value.'),
                'route' => 'commercial.reports.quotations.index',
                'permission' => 'commercial.reports.quotations.view',
            ],
            [
                'label' => __('Sales Order Reports'),
                'description' => __('Order pipeline, status, and conversion.'),
                'route' => 'commercial.reports.sales_orders.index',
                'permission' => 'commercial.reports.sales_orders.view',
            ],
            [
                'label' => __('Customer Reports'),
                'description' => __('Customer counts, revenue, and growth.'),
                'route' => 'commercial.reports.customers.index',
                'permission' => 'commercial.reports.customers.view',
            ],
            [
                'label' => __('Artwork Reports'),
                'description' => __('Design throughput and approval metrics.'),
                'route' => 'commercial.reports.artwork.index',
                'permission' => 'commercial.reports.artwork.view',
            ],
            [
                'label' => __('Conversion Reports'),
                'description' => __('Lead-to-quote and quote-to-order conversion.'),
                'route' => 'commercial.reports.conversion.index',
                'permission' => 'commercial.reports.conversion.view',
            ],
            [
                'label' => __('Commercial Workspace'),
                'description' => __('Open the Commercial module reports section.'),
                'route' => 'admin.workspaces.commercial.section',
                'permission' => 'quotations.view|crm.customers.view',
                'route_params' => ['section' => 'reports'],
            ],
        ];
    }

    /**
     * @return list<array{label: string, description: string, route: string, permission: string|null, route_params?: array<string, string>}>
     */
    public function visibleHubLinksForUser(?\App\Models\User $user): array
    {
        return collect($this->commercialReportsHubLinks())
            ->filter(function (array $link) use ($user): bool {
                if (! $user || ! ($link['permission'] ?? null)) {
                    return true;
                }

                foreach (explode('|', $link['permission']) as $permission) {
                    if ($user->can(trim($permission))) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }
}
