<?php

namespace App\Support\Commercial\Reports;

use App\Models\User;
use App\Support\Navigation\WorkspaceEmbed;
use Illuminate\Http\Request;

class CommercialReportHubPresenter
{
    /**
     * @return array<string, array{label: string, permission: string, presenter: class-string, export_route: string}>
     */
    private function reports(): array
    {
        return [
            'sales' => [
                'label' => __('Sales Reports'),
                'permission' => 'commercial.reports.sales.view',
                'presenter' => CommercialSalesReportPresenter::class,
                'export_route' => 'admin.commercial.reports.sales.export',
            ],
            'quotations' => [
                'label' => __('Quotation Reports'),
                'permission' => 'commercial.reports.quotations.view',
                'presenter' => CommercialQuotationReportPresenter::class,
                'export_route' => 'admin.commercial.reports.quotations.export',
            ],
            'sales_orders' => [
                'label' => __('Sales Order Reports'),
                'permission' => 'commercial.reports.sales_orders.view',
                'presenter' => CommercialSalesOrderReportPresenter::class,
                'export_route' => 'admin.commercial.reports.sales_orders.export',
            ],
            'customers' => [
                'label' => __('Customer Reports'),
                'permission' => 'commercial.reports.customers.view',
                'presenter' => CommercialCustomerReportPresenter::class,
                'export_route' => 'admin.commercial.reports.customers.export',
            ],
            'artwork' => [
                'label' => __('Artwork Reports'),
                'permission' => 'commercial.reports.artwork.view',
                'presenter' => CommercialArtworkReportPresenter::class,
                'export_route' => 'admin.commercial.reports.artwork.export',
            ],
            'conversion' => [
                'label' => __('Conversion Reports'),
                'permission' => 'commercial.reports.conversion.view',
                'presenter' => CommercialConversionReportPresenter::class,
                'export_route' => 'admin.commercial.reports.conversion.export',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $visible = $this->visibleReports($request->user());

        if ($visible === []) {
            return [
                'title' => __('Commercial Reports'),
                'description' => __('Departmental commercial analytics — sales, pipeline, customers, artwork, and conversion.'),
                'empty_hub' => true,
            ];
        }

        $reportKey = (string) $request->input('report', array_key_first($visible));

        if (! isset($visible[$reportKey])) {
            $reportKey = array_key_first($visible);
        }

        $definition = $visible[$reportKey];
        $presenter = app($definition['presenter']);
        $payload = $presenter->present($request);

        $hubRoute = 'admin.reports.commercial';
        $reportOptions = collect($visible)
            ->map(fn (array $report, string $key): array => [
                'key' => $key,
                'label' => $report['label'],
            ])
            ->values()
            ->all();

        $filters = array_merge($payload['filters'] ?? [], ['report' => $reportKey]);

        $hubQuery = array_filter([
            'embedded' => $request->query('embedded') === '1' || $request->input('embedded') === '1' ? '1' : null,
        ]);

        return array_merge($payload, [
            'title' => __('Commercial Reports'),
            'description' => __('Departmental commercial analytics — sales, pipeline, customers, artwork, and conversion.'),
            'report_key' => $reportKey,
            'report_view_key' => $this->reportViewKey($reportKey),
            'report_label' => $definition['label'],
            'report_options' => $reportOptions,
            'filters' => $filters,
            'hub_route' => $hubRoute,
            'index_route' => $hubRoute,
            'export_route' => $definition['export_route'],
            'filter_action' => WorkspaceEmbed::url(route($hubRoute, $hubQuery), $request),
            'filter_reset_url' => WorkspaceEmbed::url(route($hubRoute, array_merge($hubQuery, ['report' => $reportKey])), $request),
            'is_hub' => true,
        ]);
    }

    /**
     * @return array<string, array{label: string, permission: string, presenter: class-string, export_route: string}>
     */
    private function visibleReports(?User $user): array
    {
        return collect($this->reports())
            ->filter(function (array $report) use ($user): bool {
                if (! $user) {
                    return false;
                }

                return $user->can($report['permission']);
            })
            ->all();
    }

    private function reportViewKey(string $reportKey): string
    {
        return match ($reportKey) {
            'sales_orders' => 'sales-orders',
            default => $reportKey,
        };
    }
}
