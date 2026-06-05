<?php

namespace App\Support\Procurement\Reports\Exports;

use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use App\Support\Procurement\Reports\ProcurementReportPresenter;
use App\Support\Procurement\Reports\ProcurementReportQueries;
use App\Support\Procurement\Reports\ProcurementReportScope;
use Generator;

class ProcurementReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected ProcurementReportQueries $queries,
        protected ProcurementReportPresenter $presenter,
    ) {}

    public function module(): string
    {
        return 'procurement';
    }

    public function columns(CommercialReportExport $export): array
    {
        $tab = $export->tab ?: 'summary';

        return match ($tab) {
            'trends' => ['Period', 'Orders', 'Spend'],
            'supplier_spend' => ['Supplier', 'Orders', 'Spend', 'Average Order'],
            'top_suppliers' => ['Supplier', 'Orders', 'Spend'],
            'supplier_performance' => ['Supplier', 'Orders', 'Delivered', 'Late', 'Average Lead Time', 'Spend', 'Performance %'],
            'late_deliveries', 'open_orders', 'closed_orders', 'cancelled_orders' => ['PO Number', 'Supplier', 'Order Date', 'Expected Delivery', 'Status', 'Spend'],
            'cycle_time' => ['PO Number', 'Supplier', 'Order Date', 'Expected Delivery', 'First Receipt', 'Cycle Days', 'Late', 'Spend'],
            default => ['Metric', 'Value'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'supplier_spend' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapPaginator($this->queries->paginateSupplierSpend($this->withPage($scope, $page)), [
                    'supplier', 'orders', 'spend', 'average_order',
                ])
            ),
            'top_suppliers' => CommercialReportExportPaginator::yieldArray(
                $this->queries->topSuppliers($scope)->map(fn (array $row) => [
                    $row['supplier'],
                    (string) $row['orders'],
                    $this->queries->money($row['spend']),
                ])->values()->all()
            ),
            'supplier_performance' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapScorecard($this->queries->paginateSupplierScorecard($this->withPage($scope, $page)))
            ),
            'late_deliveries' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapOrderPaginator($this->queries->paginateLateDeliveries($this->withPage($scope, $page)))
            ),
            'cycle_time' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapCyclePaginator($this->queries->paginateCycleTime($this->withPage($scope, $page)))
            ),
            'open_orders' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapOrderPaginator($this->queries->paginateOpenOrders($this->withPage($scope, $page)))
            ),
            'closed_orders' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapOrderPaginator($this->queries->paginateClosedOrders($this->withPage($scope, $page)))
            ),
            'cancelled_orders' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->mapOrderPaginator($this->queries->paginateCancelledOrders($this->withPage($scope, $page)))
            ),
            'trends' => CommercialReportExportPaginator::yieldArray(
                collect($this->queries->trendSeries($scope)['monthly'] ?? [])->map(fn (array $point) => [
                    $point['label'],
                    (string) $point['orders'],
                    $this->queries->money($point['spend']),
                ])->all()
            ),
            default => CommercialReportExportPaginator::yieldArray(
                collect([
                    'orders' => __('Orders'),
                    'spend' => __('Spend'),
                    'average_order_value' => __('Average Order Value'),
                    'suppliers' => __('Active Suppliers'),
                    'open_orders' => __('Open POs'),
                    'closed_orders' => __('Closed POs'),
                    'cancelled_orders' => __('Cancelled POs'),
                    'late_deliveries' => __('Late Deliveries'),
                ])->map(function (string $label, string $key) use ($scope) {
                    $metrics = $this->queries->summaryMetrics($scope);
                    $value = $metrics[$key] ?? 0;

                    return [
                        'metric' => $label,
                        'value' => in_array($key, ['spend', 'average_order_value'], true)
                            ? $this->queries->money((float) $value)
                            : (string) $value,
                    ];
                })->values()->all()
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        $tabs = collect($this->presenter->tabs())->keyBy('key');

        return (string) ($tabs->get($export->tab)['label'] ?? __('Procurement Reports'));
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $payload = $export->scope_payload ?? [];

        return collect([
            isset($payload['from_date'], $payload['to_date'])
                ? __('Period: :from – :to', ['from' => $payload['from_date'], 'to' => $payload['to_date']])
                : null,
            ! empty($payload['branch_id']) ? __('Branch filter applied') : null,
            ! empty($payload['supplier_id']) ? __('Supplier filter applied') : null,
            ! empty($payload['warehouse_id']) ? __('Warehouse filter applied') : null,
        ])->filter()->implode(' · ');
    }

    protected function buildScope(CommercialReportExport $export): ProcurementReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new ProcurementReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: isset($payload['branch_id']) && $payload['branch_id'] !== '' ? (int) $payload['branch_id'] : null,
            fromDate: (string) ($payload['from_date'] ?? now()->startOfMonth()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            supplierId: isset($payload['supplier_id']) && $payload['supplier_id'] !== '' ? (int) $payload['supplier_id'] : null,
            warehouseId: isset($payload['warehouse_id']) && $payload['warehouse_id'] !== '' ? (int) $payload['warehouse_id'] : null,
            categoryId: isset($payload['category_id']) && $payload['category_id'] !== '' ? (int) $payload['category_id'] : null,
            search: (string) ($payload['search'] ?? ''),
            tab: (string) ($export->tab ?: 'summary'),
            topLimit: (int) ($payload['top_limit'] ?? 10),
            page: 1,
        );
    }

    protected function withPage(ProcurementReportScope $scope, int $page): ProcurementReportScope
    {
        return $this->queries->withPage($scope, $page);
    }

    /**
     * @param  list<string>  $keys
     * @return list<array<int, mixed>>
     */
    protected function mapPaginator(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, array $keys): array
    {
        return collect($paginator->items())->map(function ($row) use ($keys) {
            $data = (array) $row;

            return collect($keys)->map(function (string $key) use ($data) {
                $value = $data[$key] ?? $data[$key] ?? null;

                if (in_array($key, ['spend', 'average_order'], true)) {
                    return $this->queries->money((float) $value);
                }

                return (string) $value;
            })->all();
        })->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapScorecard(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['supplier'],
            (string) $row['orders'],
            (string) $row['delivered'],
            (string) $row['late'],
            $this->queries->days($row['average_lead_time']),
            $this->queries->money((float) $row['spend']),
            $this->queries->percent($row['performance_percent']),
        ])->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapOrderPaginator(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(function ($order) {
            return [
                $order->po_number,
                $order->vendor?->vendor_name ?? '—',
                $order->order_date?->toDateString() ?? '—',
                $order->expected_delivery_date?->toDateString() ?? '—',
                $order->status instanceof \App\Enums\PurchaseOrderStatus ? $order->status->value : (string) $order->status,
                $this->queries->money((float) $order->total_amount),
            ];
        })->all();
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function mapCyclePaginator(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (array $row) => [
            $row['po_number'],
            $row['supplier'],
            $row['order_date'] ?? '—',
            $row['expected_delivery'],
            $row['first_receipt'],
            $row['cycle_days'] === null ? '—' : (string) $row['cycle_days'],
            $row['late'],
            $this->queries->money((float) $row['spend']),
        ])->all();
    }
}
