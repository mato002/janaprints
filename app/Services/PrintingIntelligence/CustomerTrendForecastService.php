<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Crm\Customer;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use Illuminate\Support\Carbon;

class CustomerTrendForecastService
{
    /**
     * @param  array{company_id?: int, months?: int}  $filters
     * @return array<string, mixed>
     */
    public function forecast(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $months = (int) ($filters['months'] ?? 6);
        $since = now()->subMonths($months)->startOfMonth();

        $jobs = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', $since->toDateString())
            ->whereNotNull('customer_id')
            ->with('customer:id,company_name')
            ->get();

        $totalRevenue = $jobs->sum(fn ($r) => (float) $r->revenue);
        $totalProfit = $jobs->sum(fn ($r) => (float) $r->gross_profit);
        $midpoint = now()->subMonths((int) floor($months / 2))->startOfMonth();

        $trends = $jobs->groupBy('customer_id')->map(function ($group) use ($midpoint, $totalRevenue, $totalProfit) {
            $recent = $group->filter(fn ($r) => Carbon::parse($r->snapshot_date)->gte($midpoint));
            $prior = $group->filter(fn ($r) => Carbon::parse($r->snapshot_date)->lt($midpoint));

            $recentRevenue = $recent->sum(fn ($r) => (float) $r->revenue);
            $priorRevenue = $prior->sum(fn ($r) => (float) $r->revenue);
            $recentProfit = $recent->sum(fn ($r) => (float) $r->gross_profit);
            $growth = $priorRevenue > 0 ? (($recentRevenue - $priorRevenue) / $priorRevenue) * 100 : ($recentRevenue > 0 ? 100 : 0);

            return [
                'customer_id' => $group->first()->customer_id,
                'customer_name' => $group->first()->customer?->company_name ?? __('Unknown'),
                'revenue' => round($recentRevenue + $priorRevenue, 2),
                'profit' => round($recentProfit + $prior->sum(fn ($r) => (float) $r->gross_profit), 2),
                'growth_percent' => round($growth, 2),
                'revenue_contribution_percent' => $totalRevenue > 0
                    ? round((($recentRevenue + $priorRevenue) / $totalRevenue) * 100, 2)
                    : null,
                'profit_contribution_percent' => $totalProfit > 0
                    ? round((($recentProfit + $prior->sum(fn ($r) => (float) $r->gross_profit)) / $totalProfit) * 100, 2)
                    : null,
                'trend' => match (true) {
                    $growth > 15 => 'growing',
                    $growth < -15 => 'declining',
                    default => 'stable',
                },
            ];
        })->sortByDesc('growth_percent')->values();

        $concentration = $trends->sortByDesc('revenue_contribution_percent')->take(3)
            ->sum(fn ($r) => (float) ($r['revenue_contribution_percent'] ?? 0));

        return [
            'top_growth_customers' => $trends->where('trend', 'growing')->take(10)->values()->all(),
            'declining_customers' => $trends->where('trend', 'declining')->take(10)->values()->all(),
            'customer_concentration_risk_percent' => round($concentration, 2),
            'concentration_risk' => $concentration >= (float) config('printing_intelligence.customer_concentration_risk_threshold', 60),
            'rankings' => $trends->take(20)->all(),
        ];
    }
}
