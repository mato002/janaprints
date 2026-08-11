<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Live stock readiness for a job — separate from consumption completeness.
 *
 * Quotation/order stages may surface soft signals later; planning/release use
 * this as a hard gate before work reaches the floor.
 */
class MaterialReadinessService
{
    public function __construct(
        protected MaterialRequirementsService $requirements,
    ) {}

    /**
     * @return array{
     *     status: string,
     *     ready: bool,
     *     percent: int,
     *     label: string,
     *     detail: string,
     *     has_requirements: bool,
     *     line_count: int,
     *     ready_count: int,
     *     short_count: int,
     *     missing: list<array{item: string, sku: ?string, shortfall: float, unit: ?string, available: float, required: float, remaining: float}>,
     *     materials_url: string
     * }
     */
    public function assess(ProductionJobCard $jobCard): array
    {
        $rows = $this->requirements->panelRows($jobCard);

        return $this->assessFromPanelRows(
            $rows,
            route('admin.production.job-cards.show', [
                'jobCard' => $jobCard,
                'tab' => 'materials',
            ]),
        );
    }

    /**
     * Preview stock readiness from the sales order BOM before a job card exists.
     *
     * @return array{
     *     status: string,
     *     ready: bool,
     *     percent: int,
     *     label: string,
     *     detail: string,
     *     has_requirements: bool,
     *     line_count: int,
     *     ready_count: int,
     *     short_count: int,
     *     missing: list<array{item: string, sku: ?string, shortfall: float, unit: ?string, available: float, required: float, remaining: float}>,
     *     materials_url: ?string
     * }
     */
    public function previewForSalesOrder(SalesOrder $salesOrder): array
    {
        $rows = $this->requirements->previewPanelRowsForSalesOrder($salesOrder);

        return $this->assessFromPanelRows($rows, null);
    }

    /**
     * @param  array{
     *     status: string,
     *     ready: bool,
     *     percent: int,
     *     label: string,
     *     detail: string,
     *     has_requirements: bool,
     *     line_count: int,
     *     ready_count: int,
     *     short_count: int,
     *     missing: list<array{item: string, sku: ?string, shortfall: float, unit: ?string, available: float, required: float, remaining: float}>,
     *     materials_url: ?string
     * }  $assessment
     */
    public function formatBlockerMessage(array $assessment): string
    {
        if (! $assessment['has_requirements']) {
            return __('Generate material requirements and clear stock shortages before releasing this job to production.');
        }

        $summary = collect($assessment['missing'])
            ->take(3)
            ->map(function (array $line) {
                $qty = rtrim(rtrim(number_format($line['shortfall'], 3, '.', ''), '0'), '.');
                $unit = $line['unit'] ? ' '.$line['unit'] : '';

                return $line['item'].' ('.$qty.$unit.')';
            })
            ->implode(', ');

        $more = $assessment['short_count'] > 3
            ? ' '.__('and :count more', ['count' => $assessment['short_count'] - 3])
            : '';

        return __('Material readiness :percent%. Missing: :items.:more', [
            'percent' => $assessment['percent'],
            'items' => $summary !== '' ? $summary : __('stock shortages'),
            'more' => $more,
        ]);
    }

    public function assertReadyToRelease(ProductionJobCard $jobCard): void
    {
        $assessment = $this->assess($jobCard);

        if ($assessment['ready']) {
            return;
        }

        throw ValidationException::withMessages([
            'materials' => $this->formatBlockerMessage($assessment),
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{
     *     status: string,
     *     ready: bool,
     *     percent: int,
     *     label: string,
     *     detail: string,
     *     has_requirements: bool,
     *     line_count: int,
     *     ready_count: int,
     *     short_count: int,
     *     missing: list<array{item: string, sku: ?string, shortfall: float, unit: ?string, available: float, required: float, remaining: float}>,
     *     materials_url: ?string
     * }
     */
    protected function assessFromPanelRows(Collection $rows, ?string $materialsUrl): array
    {
        if ($rows->isEmpty()) {
            return [
                'status' => 'unknown',
                'ready' => false,
                'percent' => 0,
                'label' => __('Not assessed'),
                'detail' => __('Generate material requirements from the Materials tab before releasing this job to the floor.'),
                'has_requirements' => false,
                'line_count' => 0,
                'ready_count' => 0,
                'short_count' => 0,
                'missing' => [],
                'materials_url' => $materialsUrl,
            ];
        }

        $missing = $rows
            ->filter(fn (array $row) => (float) ($row['shortfall'] ?? 0) > 0)
            ->map(function (array $row) {
                return [
                    'item' => (string) ($row['item_name'] ?? __('Material')),
                    'sku' => $row['sku'] ?? null,
                    'shortfall' => (float) $row['shortfall'],
                    'unit' => $row['unit'] ?? null,
                    'available' => (float) ($row['available'] ?? 0),
                    'required' => (float) ($row['required'] ?? 0),
                    'remaining' => (float) ($row['remaining'] ?? 0),
                ];
            })
            ->values()
            ->all();

        $lineCount = $rows->count();
        $shortCount = count($missing);
        $readyCount = max(0, $lineCount - $shortCount);
        $percent = $lineCount > 0 ? (int) round(($readyCount / $lineCount) * 100) : 0;
        $ready = $shortCount === 0;

        return [
            'status' => $ready ? 'ready' : 'blocked',
            'ready' => $ready,
            'percent' => $percent,
            'label' => $ready ? __('Ready') : __('Not ready'),
            'detail' => $ready
                ? __('All material lines have enough available stock for remaining requirements.')
                : __('Release blocked until shortages are purchased, reserved, or substituted.'),
            'has_requirements' => true,
            'line_count' => $lineCount,
            'ready_count' => $readyCount,
            'short_count' => $shortCount,
            'missing' => $missing,
            'materials_url' => $materialsUrl,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function shortfallCount(Collection $rows): int
    {
        return $rows->filter(fn (array $row) => (float) ($row['shortfall'] ?? 0) > 0)->count();
    }
}
