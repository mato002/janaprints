<?php

namespace App\Support\Inventory;

use App\Models\Inventory\StockCountItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryVarianceExportService
{
    public function __construct(
        protected InventoryExportService $exports,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(string $format, int $companyId, ?int $branchId, array $filters, ?User $user = null): StreamedResponse
    {
        $report = $this->buildReport($companyId, $branchId, $filters, $user);
        $filename = 'inventory-variances-'.now()->format('Y-m-d');

        return match ($format) {
            'excel' => $this->exports->streamExcel($filename, $this->detailHeaders(), $this->detailRows($report['lines'])),
            'pdf' => $this->exports->streamHtmlDocument(
                $filename,
                view('admin.inventory.control.variances.exports.pdf', $report)->render(),
            ),
            default => $this->exports->streamCsv($filename, $this->detailHeaders(), $this->detailRows($report['lines'])),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     lines: Collection<int, StockCountItem>,
     *     summary: array<string, float>,
     *     totals: array<string, float>,
     *     meta: array<string, string|null>,
     *     generatedAt: \Illuminate\Support\Carbon,
     * }
     */
    public function buildReport(int $companyId, ?int $branchId, array $filters, ?User $user = null): array
    {
        $lines = InventoryVarianceService::exportRows($companyId, $branchId, $filters);

        $expectedQty = $lines->sum(fn (StockCountItem $line) => (float) $line->system_quantity);
        $countedQty = $lines->sum(fn (StockCountItem $line) => (float) $line->counted_quantity);
        $varianceQty = $lines->sum(fn (StockCountItem $line) => (float) $line->variance_quantity);
        $varianceCost = $lines->sum(fn (StockCountItem $line) => (float) $line->variance_value);

        $positiveValue = $lines
            ->filter(fn (StockCountItem $line) => (float) $line->variance_value > 0)
            ->sum(fn (StockCountItem $line) => (float) $line->variance_value);

        $negativeValue = $lines
            ->filter(fn (StockCountItem $line) => (float) $line->variance_value < 0)
            ->sum(fn (StockCountItem $line) => (float) $line->variance_value);

        $firstLine = $lines->first();
        $warehouseName = $firstLine?->stockCount?->warehouse?->name;
        if (! empty($filters['warehouse_id'])) {
            $warehouseName = $lines->first()?->stockCount?->warehouse?->name ?? __('Filtered warehouse');
        } elseif ($lines->pluck('stockCount.warehouse_id')->unique()->count() > 1) {
            $warehouseName = __('Multiple warehouses');
        }

        return [
            'lines' => $lines,
            'summary' => [
                'expected_qty' => round($expectedQty, 3),
                'counted_qty' => round($countedQty, 3),
                'variance_qty' => round($varianceQty, 3),
                'variance_cost' => round($varianceCost, 2),
            ],
            'totals' => [
                'positive_variance' => round($positiveValue, 2),
                'negative_variance' => round($negativeValue, 2),
                'net_variance' => round($positiveValue + $negativeValue, 2),
            ],
            'meta' => [
                'warehouse' => $warehouseName ?? __('All warehouses'),
                'count_date' => $this->formatDateRange($filters, $lines),
                'prepared_by' => $firstLine?->stockCount?->creator?->name,
                'approved_by' => $firstLine?->stockCount?->approver?->name,
                'exported_by' => $user?->name,
            ],
            'generatedAt' => now(),
        ];
    }

    /**
     * @return list<string>
     */
    protected function detailHeaders(): array
    {
        return [
            'Count', 'Warehouse', 'Count Date', 'Item', 'SKU',
            'Expected Qty', 'Counted Qty', 'Variance Qty', 'Unit Cost', 'Variance Value',
            'Reason', 'Status',
        ];
    }

    /**
     * @param  Collection<int, StockCountItem>  $lines
     * @return list<list<string|float|null>>
     */
    protected function detailRows(Collection $lines): array
    {
        return $lines->map(fn (StockCountItem $line) => [
            $line->stockCount?->count_number,
            $line->stockCount?->warehouse?->name,
            $line->stockCount?->count_date?->format('Y-m-d'),
            $line->inventoryItem?->item_name,
            $line->inventoryItem?->sku,
            $line->system_quantity,
            $line->counted_quantity,
            $line->variance_quantity,
            number_format((float) $line->system_unit_cost, 2),
            number_format((float) $line->variance_value, 2),
            $line->reason,
            $line->stockCount?->status?->value,
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Collection<int, StockCountItem>  $lines
     */
    protected function formatDateRange(array $filters, Collection $lines): string
    {
        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            return trim(($filters['date_from'] ?? '…').' — '.($filters['date_to'] ?? '…'));
        }

        $dates = $lines
            ->map(fn (StockCountItem $line) => $line->stockCount?->count_date?->format('Y-m-d'))
            ->filter()
            ->unique()
            ->values();

        if ($dates->count() === 1) {
            return (string) $dates->first();
        }

        if ($dates->isEmpty()) {
            return __('N/A');
        }

        return __('Multiple dates');
    }
}
