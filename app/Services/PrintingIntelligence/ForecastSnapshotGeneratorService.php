<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ForecastModel;
use App\Enums\ForecastPeriodType;
use App\Enums\ForecastType;
use App\Models\PrintingIntelligence\PrintForecastSnapshot;
use Illuminate\Support\Carbon;

class ForecastSnapshotGeneratorService
{
    public function __construct(
        protected RevenueForecastService $revenue,
        protected ProfitForecastService $profit,
        protected CapacityForecastService $capacity,
        protected DemandForecastService $demand,
        protected CustomerTrendForecastService $customerTrend,
        protected InventoryRiskForecastService $inventoryRisk,
    ) {}

    /**
     * @return list<PrintForecastSnapshot>
     */
    public function generateForCompany(
        int $companyId,
        ?string $forecastType = null,
        ?string $period = null,
        bool $persist = true,
    ): array {
        if (! config('printing_intelligence.executive_forecasting_enabled', true)) {
            return [];
        }

        $snapshots = [];
        $types = $forecastType ? [$forecastType] : ['revenue', 'profit', 'capacity', 'demand', 'customer', 'inventory_risk'];

        foreach ($types as $type) {
            $snapshots = array_merge($snapshots, match ($type) {
                'revenue' => $this->generateRevenue($companyId, $period, $persist),
                'profit' => $this->generateProfit($companyId, $period, $persist),
                'capacity', 'machine' => $this->generateCapacity($companyId, $period, $persist),
                'demand' => $this->generateDemand($companyId, $period, $persist),
                'customer' => $this->generateCustomer($companyId, $period, $persist),
                'inventory_risk' => $this->generateInventoryRisk($companyId, $period, $persist),
                default => [],
            });
        }

        return $snapshots;
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateRevenue(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->revenue->forecast(['company_id' => $companyId]);
        $periods = $period ? [ForecastPeriodType::from($period)] : [
            ForecastPeriodType::Month,
            ForecastPeriodType::Quarter,
            ForecastPeriodType::Year,
        ];

        $snapshots = [];
        foreach ($periods as $periodType) {
            $key = match ($periodType) {
                ForecastPeriodType::Quarter => 'next_quarter',
                ForecastPeriodType::Year => 'next_year',
                default => 'next_month',
            };
            $payload = $data[$key] ?? null;
            if ($payload) {
                $snapshots[] = $this->persist($companyId, ForecastType::Revenue, $periodType, $payload, $persist);
            }
        }

        return array_filter($snapshots);
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateProfit(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->profit->forecast(['company_id' => $companyId]);
        $payload = $data['forecast_profit'] ?? null;
        if (! $payload) {
            return [];
        }

        $periodType = $period ? ForecastPeriodType::from($period) : ForecastPeriodType::Month;

        return array_filter([$this->persist($companyId, ForecastType::Profit, $periodType, $payload, $persist)]);
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateCapacity(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->capacity->forecast(['company_id' => $companyId]);
        $payload = $data['overall_utilization_forecast'] ?? null;
        if (! $payload) {
            return [];
        }

        $range = app(ExecutiveForecastingService::class)->nextPeriodRange(
            $period ? ForecastPeriodType::from($period) : ForecastPeriodType::Month,
        );

        return array_filter([
            $this->persist($companyId, ForecastType::Capacity, ForecastPeriodType::Month, array_merge($payload, $range), $persist),
        ]);
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateDemand(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->demand->forecast(['company_id' => $companyId]);
        $top = $data['products'][0] ?? null;
        if (! $top) {
            return [];
        }

        $range = app(ExecutiveForecastingService::class)->nextPeriodRange(ForecastPeriodType::Month);

        return array_filter([
            $this->persist($companyId, ForecastType::Demand, ForecastPeriodType::Month, [
                'forecast_value' => $top['forecast_job_count'],
                'confidence_score' => $top['confidence_score'],
                'historical_periods_used' => 3,
                'forecast_model' => config('printing_intelligence.default_forecast_model', 'weighted_average'),
                'forecast_period_start' => $range['start'],
                'forecast_period_end' => $range['end'],
                'metadata' => ['product_key' => $top['product_key'], 'product_label' => $top['product_label']],
            ], $persist),
        ]);
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateCustomer(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->customerTrend->forecast(['company_id' => $companyId]);
        $range = app(ExecutiveForecastingService::class)->nextPeriodRange(ForecastPeriodType::Month);

        return array_filter([
            $this->persist($companyId, ForecastType::Customer, ForecastPeriodType::Month, [
                'forecast_value' => $data['customer_concentration_risk_percent'],
                'confidence_score' => min(100, count($data['rankings'] ?? []) * 10),
                'historical_periods_used' => count($data['rankings'] ?? []),
                'forecast_model' => 'weighted_average',
                'forecast_period_start' => $range['start'],
                'forecast_period_end' => $range['end'],
                'metadata' => [
                    'concentration_risk' => $data['concentration_risk'],
                    'top_growth' => array_slice($data['top_growth_customers'] ?? [], 0, 3),
                ],
            ], $persist),
        ]);
    }

    /**
     * @return list<PrintForecastSnapshot>
     */
    protected function generateInventoryRisk(int $companyId, ?string $period, bool $persist): array
    {
        $data = $this->inventoryRisk->forecast(['company_id' => $companyId]);
        $highest = $data['highest_risk'] ?? null;
        if (! $highest) {
            return [];
        }

        $range = app(ExecutiveForecastingService::class)->nextPeriodRange(ForecastPeriodType::Month);

        return array_filter([
            $this->persist($companyId, ForecastType::InventoryRisk, ForecastPeriodType::Month, [
                'forecast_value' => $highest['days_to_risk'],
                'confidence_score' => 75,
                'historical_periods_used' => $highest['items_tracked'] ?? 0,
                'forecast_model' => 'moving_average',
                'forecast_period_start' => $range['start'],
                'forecast_period_end' => $range['end'],
                'metadata' => $highest,
            ], $persist),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function persist(
        int $companyId,
        ForecastType $type,
        ForecastPeriodType $periodType,
        array $payload,
        bool $persist,
    ): ?PrintForecastSnapshot {
        $modelValue = $payload['forecast_model'] ?? null;
        $model = $modelValue instanceof ForecastModel
            ? $modelValue
            : (ForecastModel::tryFrom((string) $modelValue) ?? ForecastModel::WeightedAverage);

        $data = [
            'company_id' => $companyId,
            'forecast_type' => $type,
            'period_type' => $periodType,
            'forecast_period_start' => $payload['forecast_period_start'] ?? now()->addMonth()->startOfMonth()->toDateString(),
            'forecast_period_end' => $payload['forecast_period_end'] ?? now()->addMonth()->endOfMonth()->toDateString(),
            'historical_periods_used' => (int) ($payload['historical_periods_used'] ?? 0),
            'forecast_value' => $payload['forecast_value'] ?? null,
            'lower_bound' => $payload['lower_bound'] ?? null,
            'upper_bound' => $payload['upper_bound'] ?? null,
            'confidence_score' => $payload['confidence_score'] ?? null,
            'forecast_model' => $model,
            'forecast_version' => config('printing_intelligence.forecast_formula_version', 'PI9-V1'),
            'metadata' => $payload['metadata'] ?? null,
            'generated_at' => now(),
        ];

        if (! $persist) {
            return new PrintForecastSnapshot($data);
        }

        return PrintForecastSnapshot::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'forecast_type' => $type,
                'period_type' => $periodType,
                'forecast_period_start' => Carbon::parse($data['forecast_period_start'])->toDateString(),
            ],
            $data,
        );
    }
}
