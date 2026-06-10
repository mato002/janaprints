<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryVelocitySnapshot;

class InventoryRiskForecastService
{
    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    public function forecast(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $filters['branch_id'] ?? tenant()->branchId();
        $window = (int) config('inventory_intelligence.default_snapshot_window', 30);

        $latestPeriodEnd = InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $window)
            ->max('period_end');

        if ($latestPeriodEnd === null) {
            return [
                'categories' => [],
                'highest_risk' => null,
                'read_only' => true,
            ];
        }

        $snapshots = InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $window)
            ->where('period_end', $latestPeriodEnd)
            ->with('inventoryItem')
            ->get();

        $categories = [
            'paper' => ['keywords' => ['paper', 'bond', 'a4', 'a3', 'sheet']],
            'ink' => ['keywords' => ['ink', 'toner', 'cartridge', 'cmyk']],
            'consumable' => ['keywords' => ['blade', 'solvent', 'cleaning', 'laminate', 'vinyl']],
        ];

        $results = [];
        foreach ($categories as $category => $meta) {
            $items = $snapshots->filter(fn ($row) => $this->matchesCategory($row->inventoryItem, $meta['keywords']));
            $worstDays = $items->pluck('days_to_depletion')->filter()->min();
            $criticalCount = $items->filter(fn ($row) => in_array($row->risk_level?->value, ['critical', 'high'], true))->count();

            $results[$category] = [
                'category' => $category,
                'label' => match ($category) {
                    'paper' => __('Paper stockout risk'),
                    'ink' => __('Ink stockout risk'),
                    default => __('Consumable stockout risk'),
                },
                'items_tracked' => $items->count(),
                'days_to_risk' => $worstDays !== null ? round((float) $worstDays, 1) : null,
                'risk_class' => $this->riskClass($worstDays, $criticalCount),
                'critical_items' => $criticalCount,
            ];
        }

        uasort($results, fn ($a, $b) => ($a['days_to_risk'] ?? 999) <=> ($b['days_to_risk'] ?? 999));

        return [
            'categories' => array_values($results),
            'highest_risk' => reset($results) ?: null,
            'read_only' => true,
        ];
    }

    protected function matchesCategory(?InventoryItem $item, array $keywords): bool
    {
        if ($item === null) {
            return false;
        }

        $haystack = strtolower(($item->item_name ?? '').' '.($item->sku ?? ''));

        foreach ($keywords as $keyword) {
            if (str_contains($haystack, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function riskClass(mixed $daysToRisk, int $criticalCount): string
    {
        if ($criticalCount > 0 || ($daysToRisk !== null && $daysToRisk <= 7)) {
            return 'critical';
        }
        if ($daysToRisk !== null && $daysToRisk <= 14) {
            return 'high';
        }
        if ($daysToRisk !== null && $daysToRisk <= 30) {
            return 'medium';
        }

        return 'low';
    }
}
