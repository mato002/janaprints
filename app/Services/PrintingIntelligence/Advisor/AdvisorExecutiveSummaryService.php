<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorSeverity;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;

class AdvisorExecutiveSummaryService
{
    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    public function summarize(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $filters['branch_id'] ?? null;

        $query = PrintAdvisorRecommendation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', AdvisorRecommendationStatus::Open);

        $open = (clone $query)->get();

        return [
            'top_opportunities' => $open->where('severity', AdvisorSeverity::Info)->sortByDesc('confidence_score')->take(5)->values()->all(),
            'top_risks' => $open->whereIn('severity', [AdvisorSeverity::High, AdvisorSeverity::Critical])->sortByDesc('confidence_score')->take(5)->values()->all(),
            'top_margin_threats' => $open->filter(fn ($r) => in_array($r->recommendation_type->value, ['quotation', 'profitability'], true))
                ->sortByDesc('confidence_score')->take(5)->values()->all(),
            'top_growth_areas' => $open->filter(fn ($r) => in_array($r->recommendation_type->value, ['forecast', 'customer'], true))
                ->sortByDesc('confidence_score')->take(5)->values()->all(),
            'top_inventory_risks' => $open->filter(fn ($r) => $r->recommendation_type->value === 'inventory')
                ->sortByDesc('confidence_score')->take(5)->values()->all(),
            'top_capacity_risks' => $open->filter(fn ($r) => in_array($r->recommendation_type->value, ['machine', 'production'], true))
                ->sortByDesc('confidence_score')->take(5)->values()->all(),
            'open_count' => $open->count(),
            'read_only' => true,
        ];
    }
}
