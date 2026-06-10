<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ForecastModel;
use App\Enums\ForecastPeriodType;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use Illuminate\Support\Carbon;

class ExecutiveForecastingService
{
    public function __construct(
        protected ForecastConfidenceService $confidence,
    ) {}

    /**
     * @return list<array{period: string, value: float}>
     */
    public function monthlyMetricSeries(int $companyId, string $metric = 'revenue', int $months = 12): array
    {
        $since = now()->subMonths($months)->startOfMonth();

        $rows = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Job)
            ->where('snapshot_date', '>=', $since->toDateString())
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->snapshot_date)->format('Y-m'));

        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $group = $rows->get($key, collect());
            $value = match ($metric) {
                'profit' => $group->sum(fn ($r) => (float) $r->gross_profit),
                'margin' => $this->weightedMargin($group),
                'job_count' => (float) $group->count(),
                default => $group->sum(fn ($r) => (float) $r->revenue),
            };
            $series[] = ['period' => $key, 'value' => round((float) $value, 2)];
        }

        return $series;
    }

    /**
     * @param  list<float|int>  $values
     * @return array<string, mixed>
     */
    public function project(array $values, ?ForecastModel $model = null): array
    {
        $model ??= ForecastModel::tryFrom(
            config('printing_intelligence.default_forecast_model', 'weighted_average')
        ) ?? ForecastModel::WeightedAverage;

        $nonZero = array_values(array_filter($values, fn ($v) => $v !== null));
        $count = count($nonZero);

        if ($count === 0) {
            return [
                'forecast_value' => null,
                'lower_bound' => null,
                'upper_bound' => null,
                'historical_periods_used' => 0,
                'forecast_model' => $model,
                'confidence_score' => 0,
                'confidence_band' => 'low',
            ];
        }

        $forecast = match ($model) {
            ForecastModel::MovingAverage => $this->movingAverage($nonZero),
            ForecastModel::TrendProjection => $this->trendProjection($nonZero),
            default => $this->weightedAverage($nonZero),
        };

        $spread = $this->spread($nonZero, $forecast);
        $confidence = $this->confidence->score([
            'periods_with_data' => $count,
            'historical_periods' => $count,
            'values' => $nonZero,
        ]);

        return [
            'forecast_value' => round($forecast, 2),
            'lower_bound' => round(max(0, $forecast - $spread), 2),
            'upper_bound' => round($forecast + $spread, 2),
            'historical_periods_used' => $count,
            'forecast_model' => $model,
            'confidence_score' => $confidence,
            'confidence_band' => $this->confidence->band($confidence),
        ];
    }

    /**
     * @param  list<float|int>  $values
     */
    public function movingAverage(array $values): float
    {
        return array_sum($values) / max(1, count($values));
    }

    /**
     * @param  list<float|int>  $values
     */
    public function weightedAverage(array $values): float
    {
        $weighted = 0;
        $weightSum = 0;
        foreach ($values as $index => $value) {
            $weight = $index + 1;
            $weighted += (float) $value * $weight;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? $weighted / $weightSum : 0;
    }

    /**
     * @param  list<float|int>  $values
     */
    public function trendProjection(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return (float) ($values[0] ?? 0);
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        foreach ($values as $i => $y) {
            $x = $i + 1;
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2) - ($sumX ** 2);
        if (abs($denominator) < 0.0001) {
            return $this->movingAverage($values);
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        return $intercept + ($slope * ($n + 1));
    }

    /**
     * @return array{start: string, end: string}
     */
    public function nextPeriodRange(ForecastPeriodType $periodType): array
    {
        return match ($periodType) {
            ForecastPeriodType::Quarter => [
                'start' => now()->addQuarter()->startOfQuarter()->toDateString(),
                'end' => now()->addQuarter()->endOfQuarter()->toDateString(),
            ],
            ForecastPeriodType::Year => [
                'start' => now()->addYear()->startOfYear()->toDateString(),
                'end' => now()->addYear()->endOfYear()->toDateString(),
            ],
            default => [
                'start' => now()->addMonth()->startOfMonth()->toDateString(),
                'end' => now()->addMonth()->endOfMonth()->toDateString(),
            ],
        };
    }

    /**
     * @param  list<float|int>  $values
     */
    protected function spread(array $values, float $forecast): float
    {
        if (count($values) < 2) {
            return abs($forecast) * 0.15;
        }

        $mean = array_sum($values) / count($values);
        $variance = 0;
        foreach ($values as $value) {
            $variance += ((float) $value - $mean) ** 2;
        }
        $stdDev = sqrt($variance / count($values));

        return max(abs($forecast) * 0.1, $stdDev);
    }

    protected function weightedMargin($rows): float
    {
        $revenue = $rows->sum(fn ($r) => (float) $r->revenue);
        $profit = $rows->sum(fn ($r) => (float) $r->gross_profit);

        return $revenue > 0 ? round(($profit / $revenue) * 100, 3) : 0;
    }
}
