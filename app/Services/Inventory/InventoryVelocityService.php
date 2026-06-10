<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryRiskLevel;
use App\Enums\InventoryVelocityClass;
use App\Enums\ReorderAlertStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\InventoryMovementAnalytics;
use App\Support\InventoryStockService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryVelocityService
{
    /**
     * @return array{
     *     items_processed: int,
     *     critical_risks: int,
     *     dead_stock_count: int,
     *     fast_movers: int,
     *     duration_ms: float,
     *     windows: list<int>
     * }
     */
    public function generateSnapshots(
        int $companyId,
        ?int $branchId = null,
        int|array $windows = 30,
        ?int $warehouseId = null,
        bool $dryRun = false,
        bool $syncAlerts = true,
    ): array {
        $started = microtime(true);
        $windowList = is_array($windows) ? $windows : [$windows];
        $periodEnd = today();

        $items = $this->itemsQuery($companyId, $branchId)->get();
        $warehouses = $this->warehousesQuery($companyId, $branchId, $warehouseId)->get();

        $processed = 0;
        $criticalRisks = 0;
        $deadStockCount = 0;
        $fastMovers = 0;

        foreach ($windowList as $windowDays) {
            $periodStart = $periodEnd->copy()->subDays($windowDays);

            foreach ($items as $item) {
                foreach ($warehouses as $warehouse) {
                    if (! $this->shouldProcessPair($item, $warehouse, $periodStart, $periodEnd)) {
                        continue;
                    }

                    $metrics = $this->calculateMetrics($item, $warehouse, $periodStart, $periodEnd, $windowDays);
                    $processed++;

                    if ($metrics['risk_level'] === InventoryRiskLevel::Critical) {
                        $criticalRisks++;
                    }
                    if ($metrics['velocity_class'] === InventoryVelocityClass::DeadStock) {
                        $deadStockCount++;
                    }
                    if ($metrics['velocity_class'] === InventoryVelocityClass::FastMoving) {
                        $fastMovers++;
                    }

                    if (! $dryRun) {
                        $this->persistSnapshot($item, $warehouse, $periodStart, $periodEnd, $windowDays, $metrics);

                        if ($syncAlerts && $windowDays === (int) config('inventory_intelligence.default_snapshot_window', 30)) {
                            $this->syncVelocityAlert($item, $warehouse, $metrics);
                        }
                    }
                }
            }
        }

        return [
            'items_processed' => $processed,
            'critical_risks' => $criticalRisks,
            'dead_stock_count' => $deadStockCount,
            'fast_movers' => $fastMovers,
            'duration_ms' => round((microtime(true) - $started) * 1000, 2),
            'windows' => $windowList,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateMetrics(
        InventoryItem $item,
        Warehouse $warehouse,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $windowDays,
    ): array {
        $movements = InventoryMovement::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->whereBetween('movement_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get(['movement_type', 'quantity', 'movement_date']);

        $allMovements = InventoryMovement::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->get(['movement_type', 'quantity', 'movement_date']);

        $totalIn = 0.0;
        $totalOut = 0.0;
        $lastInbound = null;
        $lastOutbound = null;

        foreach ($movements as $movement) {
            $type = $movement->movement_type instanceof InventoryMovementType
                ? $movement->movement_type
                : InventoryMovementType::from((string) $movement->movement_type);
            $qty = (float) $movement->quantity;
            $date = Carbon::parse($movement->movement_date);

            if (InventoryMovementAnalytics::isInboundMovement($type, $qty)) {
                $totalIn += abs($qty);
                $lastInbound = $this->latestTimestamp($lastInbound, $date);
            }

            if (InventoryMovementAnalytics::isOutboundMovement($type, $qty)) {
                $totalOut += abs($qty);
                $lastOutbound = $this->latestTimestamp($lastOutbound, $date);
            }

            if ($type === InventoryMovementType::Adjustment) {
                if ($qty > 0) {
                    $totalIn += $qty;
                    $lastInbound = $this->latestTimestamp($lastInbound, $date);
                } elseif ($qty < 0) {
                    $totalOut += abs($qty);
                    $lastOutbound = $this->latestTimestamp($lastOutbound, $date);
                }
            }
        }

        $globalLastOutbound = null;
        $globalLastInbound = null;
        $globalLastMovement = null;

        foreach ($allMovements as $movement) {
            $type = $movement->movement_type instanceof InventoryMovementType
                ? $movement->movement_type
                : InventoryMovementType::from((string) $movement->movement_type);
            $qty = (float) $movement->quantity;
            $date = Carbon::parse($movement->movement_date);
            $globalLastMovement = $this->latestTimestamp($globalLastMovement, $date);

            if (InventoryMovementAnalytics::isInboundMovement($type, $qty) || ($type === InventoryMovementType::Adjustment && $qty > 0)) {
                $globalLastInbound = $this->latestTimestamp($globalLastInbound, $date);
            }

            if (InventoryMovementAnalytics::isOutboundMovement($type, $qty) || ($type === InventoryMovementType::Adjustment && $qty < 0)) {
                $globalLastOutbound = $this->latestTimestamp($globalLastOutbound, $date);
            }
        }

        $closingBalance = InventoryStockService::balance($item->id, $warehouse->id);
        $openingBalance = (float) InventoryMovement::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('movement_date', '<', $periodStart->toDateString())
            ->sum('quantity');

        $averageDailyConsumption = $windowDays > 0 ? round($totalOut / $windowDays, 4) : 0.0;
        $averageWeeklyConsumption = round($averageDailyConsumption * 7, 4);
        $daysToDepletion = $averageDailyConsumption > 0
            ? round($closingBalance / $averageDailyConsumption, 2)
            : null;

        $velocityClass = $this->classifyVelocity(
            $item,
            $totalOut,
            $averageDailyConsumption,
            $closingBalance,
            $globalLastOutbound,
            $allMovements->isNotEmpty(),
        );

        $riskLevel = $this->classifyRisk($daysToDepletion, $closingBalance, $averageDailyConsumption, $velocityClass);

        return [
            'stock_role' => $item->stock_role?->value ?? (string) $item->stock_role,
            'opening_balance' => round($openingBalance, 3),
            'closing_balance' => round($closingBalance, 3),
            'total_in_quantity' => round($totalIn, 3),
            'total_out_quantity' => round($totalOut, 3),
            'net_quantity' => round($totalIn - $totalOut, 3),
            'average_daily_consumption' => $averageDailyConsumption,
            'average_weekly_consumption' => $averageWeeklyConsumption,
            'days_to_depletion' => $daysToDepletion,
            'velocity_class' => $velocityClass,
            'risk_level' => $riskLevel,
            'last_inbound_at' => $lastInbound ?? $globalLastInbound,
            'last_outbound_at' => $lastOutbound ?? $globalLastOutbound,
            'last_movement_at' => $globalLastMovement,
            'metadata' => [
                'window_outbound' => round($totalOut, 3),
                'window_inbound' => round($totalIn, 3),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    protected function persistSnapshot(
        InventoryItem $item,
        Warehouse $warehouse,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $windowDays,
        array $metrics,
    ): InventoryVelocitySnapshot {
        $attributes = [
            'branch_id' => $item->branch_id,
            'stock_role' => $metrics['stock_role'],
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'opening_balance' => $metrics['opening_balance'],
            'closing_balance' => $metrics['closing_balance'],
            'total_in_quantity' => $metrics['total_in_quantity'],
            'total_out_quantity' => $metrics['total_out_quantity'],
            'net_quantity' => $metrics['net_quantity'],
            'average_daily_consumption' => $metrics['average_daily_consumption'],
            'average_weekly_consumption' => $metrics['average_weekly_consumption'],
            'days_to_depletion' => $metrics['days_to_depletion'],
            'velocity_class' => $metrics['velocity_class'],
            'risk_level' => $metrics['risk_level'],
            'last_inbound_at' => $metrics['last_inbound_at'],
            'last_outbound_at' => $metrics['last_outbound_at'],
            'last_movement_at' => $metrics['last_movement_at'],
            'metadata' => $metrics['metadata'],
            'generated_at' => now(),
        ];

        $snapshot = InventoryVelocitySnapshot::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('movement_window_days', $windowDays)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if ($snapshot !== null) {
            $snapshot->update($attributes);

            return $snapshot->fresh();
        }

        return InventoryVelocitySnapshot::query()->create(array_merge([
            'company_id' => $item->company_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_window_days' => $windowDays,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    protected function syncVelocityAlert(InventoryItem $item, Warehouse $warehouse, array $metrics): void
    {
        $alertType = (string) config('inventory_intelligence.velocity_alert_type', 'velocity_stockout_risk');
        $risk = $metrics['risk_level'];
        $adc = (float) $metrics['average_daily_consumption'];
        $balance = (float) $metrics['closing_balance'];

        $shouldAlert = $adc > 0
            && $balance > 0
            && in_array($risk, [InventoryRiskLevel::Medium, InventoryRiskLevel::High, InventoryRiskLevel::Critical], true);

        $existing = InventoryReorderAlert::query()
            ->where('company_id', $item->company_id)
            ->where('branch_id', $item->branch_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('alert_type', $alertType)
            ->first();

        if (! $shouldAlert) {
            if ($existing !== null && $existing->status->isActionable()) {
                $existing->update([
                    'status' => ReorderAlertStatus::Resolved,
                    'is_resolved' => true,
                    'resolved_at' => now(),
                ]);
            }

            return;
        }

        $coverDays = (int) config('inventory_intelligence.default_reorder_cover_days', 30);
        $recommendedQty = round($adc * $coverDays, 3);
        $daysToDepletion = $metrics['days_to_depletion'];
        $recommendedBy = $daysToDepletion !== null
            ? today()->addDays((int) max(0, floor((float) $daysToDepletion)))->toDateString()
            : null;

        InventoryReorderAlert::query()->updateOrCreate(
            [
                'company_id' => $item->company_id,
                'branch_id' => $item->branch_id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'alert_type' => $alertType,
            ],
            [
                'current_quantity' => $balance,
                'reorder_level' => 0,
                'recommended_quantity' => $recommendedQty,
                'is_resolved' => false,
                'status' => $existing?->status === ReorderAlertStatus::Acknowledged
                    ? ReorderAlertStatus::Acknowledged
                    : ReorderAlertStatus::Open,
                'alerted_at' => $existing?->alerted_at ?? now(),
                'metadata' => [
                    'days_to_depletion' => $daysToDepletion,
                    'average_daily_consumption' => $adc,
                    'current_balance' => $balance,
                    'risk_level' => $risk->value,
                    'recommended_reorder_quantity' => $recommendedQty,
                    'recommended_reorder_by' => $recommendedBy,
                ],
            ],
        );
    }

    protected function classifyVelocity(
        InventoryItem $item,
        float $totalOut,
        float $averageDailyConsumption,
        float $closingBalance,
        ?Carbon $lastOutbound,
        bool $hasAnyMovement,
    ): InventoryVelocityClass {
        $graceDays = (int) config('inventory_intelligence.new_item_grace_days', 14);
        if ($item->created_at !== null && $item->created_at->gte(now()->subDays($graceDays))) {
            return InventoryVelocityClass::NewItem;
        }

        $deadStockDays = (int) config('inventory_intelligence.dead_stock_days', 60);
        if ($closingBalance > 0) {
            $daysSinceOutbound = $lastOutbound === null
                ? PHP_INT_MAX
                : $lastOutbound->diffInDays(today());

            if ($daysSinceOutbound >= $deadStockDays) {
                return InventoryVelocityClass::DeadStock;
            }
        }

        if (! $hasAnyMovement || ($totalOut <= 0 && abs($closingBalance) < 0.001)) {
            return InventoryVelocityClass::NoData;
        }

        $fastThreshold = (float) config('inventory_intelligence.fast_moving_daily_threshold', 5.0);
        $slowThreshold = (float) config('inventory_intelligence.slow_moving_daily_threshold', 0.1);

        if ($averageDailyConsumption >= $fastThreshold) {
            return InventoryVelocityClass::FastMoving;
        }

        if ($totalOut > 0 && $averageDailyConsumption < $slowThreshold) {
            return InventoryVelocityClass::SlowMoving;
        }

        return InventoryVelocityClass::Normal;
    }

    protected function classifyRisk(
        ?float $daysToDepletion,
        float $closingBalance,
        float $averageDailyConsumption,
        InventoryVelocityClass $velocityClass,
    ): InventoryRiskLevel {
        if ($velocityClass === InventoryVelocityClass::DeadStock) {
            return InventoryRiskLevel::Low;
        }

        if ($closingBalance <= 0) {
            return InventoryRiskLevel::Critical;
        }

        if ($averageDailyConsumption <= 0 || $daysToDepletion === null) {
            return InventoryRiskLevel::Low;
        }

        if ($daysToDepletion <= (float) config('inventory_intelligence.critical_days_to_depletion', 3)) {
            return InventoryRiskLevel::Critical;
        }

        if ($daysToDepletion <= (float) config('inventory_intelligence.high_days_to_depletion', 7)) {
            return InventoryRiskLevel::High;
        }

        if ($daysToDepletion <= (float) config('inventory_intelligence.medium_days_to_depletion', 14)) {
            return InventoryRiskLevel::Medium;
        }

        return InventoryRiskLevel::Low;
    }

    protected function shouldProcessPair(
        InventoryItem $item,
        Warehouse $warehouse,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): bool {
        $balance = InventoryStockService::balance($item->id, $warehouse->id);

        if (abs($balance) >= 0.001) {
            return true;
        }

        return InventoryMovement::query()
            ->where('company_id', $item->company_id)
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->whereBetween('movement_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->exists();
    }

    protected function latestTimestamp(?Carbon $current, Carbon $candidate): Carbon
    {
        return $current === null || $candidate->gt($current) ? $candidate : $current;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<InventoryItem>
     */
    protected function itemsQuery(int $companyId, ?int $branchId)
    {
        return InventoryItem::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Warehouse>
     */
    protected function warehousesQuery(int $companyId, ?int $branchId, ?int $warehouseId)
    {
        return Warehouse::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($warehouseId, fn ($q) => $q->whereKey($warehouseId))
            ->where('is_active', true);
    }

    /**
     * @return array{
     *     critical_stockout: int,
     *     high_risk: int,
     *     dead_stock: int,
     *     fast_moving: int,
     *     dead_stock_value: float,
     *     average_days_to_depletion: float|null
     * }
     */
    public function overviewCounts(int $companyId, ?int $branchId, int $windowDays = 30): array
    {
        $query = InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $windowDays)
            ->where('period_end', today()->toDateString());

        $deadStockValue = (float) InventoryVelocitySnapshot::query()
            ->where('inventory_velocity_snapshots.company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('inventory_velocity_snapshots.branch_id', $branchId))
            ->where('inventory_velocity_snapshots.movement_window_days', $windowDays)
            ->where('inventory_velocity_snapshots.period_end', today()->toDateString())
            ->where('inventory_velocity_snapshots.velocity_class', InventoryVelocityClass::DeadStock)
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_velocity_snapshots.inventory_item_id')
            ->selectRaw('SUM(inventory_velocity_snapshots.closing_balance * inventory_items.standard_cost) as value')
            ->value('value');

        $avgDays = (clone $query)
            ->whereNotNull('days_to_depletion')
            ->where('average_daily_consumption', '>', 0)
            ->avg('days_to_depletion');

        return [
            'critical_stockout' => (clone $query)->where('risk_level', InventoryRiskLevel::Critical)->count(),
            'high_risk' => (clone $query)->where('risk_level', InventoryRiskLevel::High)->count(),
            'dead_stock' => (clone $query)->where('velocity_class', InventoryVelocityClass::DeadStock)->count(),
            'fast_moving' => (clone $query)->where('velocity_class', InventoryVelocityClass::FastMoving)->count(),
            'dead_stock_value' => round($deadStockValue, 2),
            'average_days_to_depletion' => $avgDays !== null ? round((float) $avgDays, 1) : null,
        ];
    }

    /**
     * @return Collection<int, InventoryVelocitySnapshot>
     */
    public function latestSnapshots(
        int $companyId,
        ?int $branchId,
        int $windowDays = 30,
        ?InventoryVelocityClass $velocityClass = null,
        ?InventoryRiskLevel $riskLevel = null,
        int $limit = 50,
    ): Collection {
        return InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $windowDays)
            ->where('period_end', today()->toDateString())
            ->when($velocityClass, fn ($q) => $q->where('velocity_class', $velocityClass))
            ->when($riskLevel, fn ($q) => $q->where('risk_level', $riskLevel))
            ->with(['inventoryItem.category', 'warehouse'])
            ->orderByRaw('days_to_depletion IS NULL, days_to_depletion ASC')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array{warehouse: string, total_outbound: float, item_count: int, avg_daily_consumption: float}>
     */
    public function warehouseVelocitySummary(int $companyId, ?int $branchId, int $windowDays = 30): array
    {
        return InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('movement_window_days', $windowDays)
            ->where('period_end', today()->toDateString())
            ->with('warehouse:id,name')
            ->get()
            ->groupBy('warehouse_id')
            ->map(function (Collection $rows) {
                $warehouse = $rows->first()?->warehouse;

                return [
                    'warehouse' => $warehouse?->name ?? __('Unknown'),
                    'total_outbound' => round((float) $rows->sum('total_out_quantity'), 3),
                    'item_count' => $rows->count(),
                    'avg_daily_consumption' => round((float) $rows->avg('average_daily_consumption'), 4),
                ];
            })
            ->sortByDesc('total_outbound')
            ->values()
            ->all();
    }
}
