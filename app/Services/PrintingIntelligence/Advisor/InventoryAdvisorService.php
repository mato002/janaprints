<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Services\PrintingIntelligence\InventoryRiskForecastService;

class InventoryAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $filters['branch_id'] ?? tenant()->branchId();
        $filters['company_id'] = $companyId;
        $filters['branch_id'] = $branchId;

        $risk = app(InventoryRiskForecastService::class)->forecast($filters);
        $recommendations = [];

        foreach ($risk['categories'] ?? [] as $category) {
            if (in_array($category['risk_class'] ?? '', ['critical', 'high'], true)) {
                $days = $category['days_to_risk'] ?? null;
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Inventory,
                    $category['risk_class'] === 'critical' ? AdvisorSeverity::Critical : AdvisorSeverity::High,
                    'inventory:stockout:'.($category['category'] ?? 'general'),
                    __('Stockout risk warning'),
                    __(':label may reach risk threshold in :days days.', [
                        'label' => $category['label'] ?? __('Material'),
                        'days' => $days ?? '—',
                    ]),
                    __('Review procurement timing for :category materials before production commitments.', ['category' => $category['category'] ?? 'inventory']),
                    'PI9',
                    $this->confidence->score(['forecast_confidence' => 70, 'data_points' => 2]),
                    __('Monitor inventory velocity and plan replenishment.'),
                    null,
                    null,
                    ['category' => $category],
                );
            }
        }

        $window = (int) config('inventory_intelligence.default_snapshot_window', 30);
        $latestPeriodEnd = InventoryVelocitySnapshot::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->max('period_end');

        if ($latestPeriodEnd) {
            $fastItems = InventoryVelocitySnapshot::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('period_end', $latestPeriodEnd)
                ->where('velocity_class', 'fast_moving')
                ->with('inventoryItem')
                ->limit(10)
                ->get();

            foreach ($fastItems as $snapshot) {
                $item = $snapshot->inventoryItem;
                if ($item === null) {
                    continue;
                }

                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Inventory,
                    AdvisorSeverity::Info,
                    "inventory:high_velocity:{$item->id}",
                    __('High velocity alert'),
                    __(':name is fast-moving with :days days to depletion.', [
                        'name' => $item->item_name,
                        'days' => $snapshot->days_to_depletion ?? '—',
                    ]),
                    __('Ensure buffer stock for this material to avoid production delays.'),
                    'I6',
                    $this->confidence->score(['data_points' => 2, 'historical_periods' => 2]),
                    __('Review reorder point with storekeeper.'),
                    InventoryItem::class,
                    $item->id,
                    ['velocity_class' => 'fast_moving'],
                );
            }

            $criticalItems = InventoryVelocitySnapshot::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('period_end', $latestPeriodEnd)
                ->whereIn('risk_level', ['critical', 'high'])
                ->with('inventoryItem')
                ->limit(10)
                ->get();

            foreach ($criticalItems as $snapshot) {
                $item = $snapshot->inventoryItem;
                if ($item === null) {
                    continue;
                }

                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Inventory,
                    AdvisorSeverity::High,
                    "inventory:item_risk:{$item->id}",
                    __('Material dependency warning'),
                    __(':name stockout risk in :days days.', [
                        'name' => $item->item_name,
                        'days' => $snapshot->days_to_depletion ?? 9,
                    ]),
                    __('Banner Vinyl and similar substrates may block multiple product lines if depleted.'),
                    'I6',
                    $this->confidence->score(['forecast_confidence' => 75, 'signal_strength' => 80]),
                    __('Flag dependent quotations referencing this material.'),
                    InventoryItem::class,
                    $item->id,
                    ['days_to_depletion' => $snapshot->days_to_depletion],
                );
            }
        }

        return $recommendations;
    }
}
