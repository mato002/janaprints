<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use Illuminate\Support\Carbon;

class DemandForecastService
{
    public function __construct(
        protected ExecutiveForecastingService $engine,
    ) {}

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
            ->get();

        $productKeys = array_keys(config('printing_intelligence.product_type_labels', []));
        $forecasts = [];

        foreach ($productKeys as $key) {
            $recent = $this->monthlyJobCounts($jobs, $key, 3);
            $prior = $this->monthlyJobCounts($jobs, $key, 3, 3);
            $projection = $this->engine->project($recent);
            $recentTotal = array_sum($recent);
            $priorTotal = array_sum($prior);
            $growth = $priorTotal > 0 ? (($recentTotal - $priorTotal) / $priorTotal) * 100 : ($recentTotal > 0 ? 100 : 0);

            $forecasts[] = [
                'product_key' => $key,
                'product_label' => config("printing_intelligence.product_type_labels.{$key}", ucfirst(str_replace('_', ' ', $key))),
                'recent_job_count' => $recentTotal,
                'forecast_job_count' => $projection['forecast_value'],
                'growth_percent' => round($growth, 2),
                'trend' => match (true) {
                    $growth > 10 => 'growing',
                    $growth < -10 => 'declining',
                    default => 'stable',
                },
                'confidence_score' => $projection['confidence_score'],
            ];
        }

        usort($forecasts, fn ($a, $b) => ($b['growth_percent'] ?? 0) <=> ($a['growth_percent'] ?? 0));

        return [
            'products' => $forecasts,
            'growing_demand' => array_values(array_filter($forecasts, fn ($r) => $r['trend'] === 'growing')),
            'declining_demand' => array_values(array_filter($forecasts, fn ($r) => $r['trend'] === 'declining')),
        ];
    }

    /**
     * @return list<float>
     */
    protected function monthlyJobCounts($jobs, string $productKey, int $months, int $offsetMonths = 0): array
    {
        $counts = [];
        for ($i = $months + $offsetMonths - 1; $i >= $offsetMonths; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $counts[] = (float) $jobs->filter(
                fn ($row) => Carbon::parse($row->snapshot_date)->format('Y-m') === $key
                    && ($row->metadata['production_type'] ?? 'unknown') === $productKey,
            )->count();
        }

        return $counts;
    }
}
