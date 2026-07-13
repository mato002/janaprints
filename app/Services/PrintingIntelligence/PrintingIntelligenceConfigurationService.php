<?php

namespace App\Services\PrintingIntelligence;

use App\Support\Platform\SystemSettingsService;
use Illuminate\Support\Facades\Cache;

class PrintingIntelligenceConfigurationService
{
    /**
     * @return array<string, array{label: string, type: string, default: mixed, group: string}>
     */
    public function editableDefinitions(): array
    {
        return [
            'default_margin_percent' => [
                'label' => 'Default margin %',
                'type' => 'float',
                'default' => config('printing_intelligence.default_margin_percent'),
                'group' => 'pricing',
            ],
            'default_target_margin_percent' => [
                'label' => 'Target margin %',
                'type' => 'float',
                'default' => config('printing_intelligence.default_target_margin_percent'),
                'group' => 'pricing',
            ],
            'default_minimum_margin_percent' => [
                'label' => 'Minimum margin %',
                'type' => 'float',
                'default' => config('printing_intelligence.default_minimum_margin_percent'),
                'group' => 'pricing',
            ],
            'electricity_rate_per_kwh' => [
                'label' => 'Electricity rate (per kWh)',
                'type' => 'float',
                'default' => config('printing_intelligence.electricity_rate_per_kwh'),
                'group' => 'costing',
            ],
            'labour_hourly_rate' => [
                'label' => 'Labour hourly rate',
                'type' => 'float',
                'default' => config('printing_intelligence.labour_hourly_rate'),
                'group' => 'costing',
            ],
            'default_wastage_percent' => [
                'label' => 'Default wastage %',
                'type' => 'float',
                'default' => config('printing_intelligence.default_wastage_percent'),
                'group' => 'costing',
            ],
            'artwork_analysis_enabled' => [
                'label' => 'Artwork analysis enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.artwork_analysis_enabled'),
                'group' => 'features',
            ],
            'colour_analysis_enabled' => [
                'label' => 'Colour analysis enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.colour_analysis_enabled'),
                'group' => 'features',
            ],
            'ink_costing_enabled' => [
                'label' => 'Ink costing enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.ink_costing_enabled'),
                'group' => 'features',
            ],
            'production_costing_enabled' => [
                'label' => 'Production costing enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.production_costing_enabled'),
                'group' => 'features',
            ],
            'quotation_estimation_enabled' => [
                'label' => 'Quotation estimation enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.quotation_estimation_enabled'),
                'group' => 'features',
            ],
            'estimate_actual_learning_enabled' => [
                'label' => 'Estimate vs actual learning enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.estimate_actual_learning_enabled'),
                'group' => 'features',
            ],
            'calibration_recommendation_enabled' => [
                'label' => 'Calibration recommendations enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.calibration_recommendation_enabled'),
                'group' => 'features',
            ],
            'profitability_intelligence_enabled' => [
                'label' => 'Profitability intelligence enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.profitability_intelligence_enabled'),
                'group' => 'features',
            ],
            'executive_forecasting_enabled' => [
                'label' => 'Executive forecasting enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.executive_forecasting_enabled'),
                'group' => 'features',
            ],
            'advisor_enabled' => [
                'label' => 'Operations advisor enabled',
                'type' => 'boolean',
                'default' => config('printing_intelligence.advisor_enabled'),
                'group' => 'features',
            ],
            'allow_apply_to_quotation' => [
                'label' => 'Allow apply estimate to quotation',
                'type' => 'boolean',
                'default' => config('printing_intelligence.allow_apply_to_quotation'),
                'group' => 'features',
            ],
            'async_analysis_enabled' => [
                'label' => 'Run artwork analysis in background queue',
                'type' => 'boolean',
                'default' => config('printing_intelligence.async_analysis_enabled'),
                'group' => 'operations',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function effectiveConfig(?int $companyId = null): array
    {
        $base = config('printing_intelligence', []);
        $companyId ??= tenant()->companyId();

        if (! $companyId) {
            return $base;
        }

        foreach ($this->editableDefinitions() as $key => $definition) {
            $stored = $this->settings->get(
                $this->storageKey($key),
                null,
                $companyId,
                null,
            );

            if ($stored !== null) {
                $base[$key] = $this->castStoredValue($stored, $definition['type']);
            }
        }

        return $base;
    }

    /**
     * @return list<array{key: string, label: string, type: string, value: mixed, default: mixed, group: string}>
     */
    public function formRows(?int $companyId = null): array
    {
        $companyId ??= tenant()->companyId();
        $effective = $this->effectiveConfig($companyId);

        return collect($this->editableDefinitions())
            ->map(fn (array $definition, string $key) => [
                'key' => $key,
                'label' => __($definition['label']),
                'type' => $definition['type'],
                'value' => $effective[$key] ?? $definition['default'],
                'default' => $definition['default'],
                'group' => $definition['group'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(int $companyId, array $values): void
    {
        foreach ($this->editableDefinitions() as $key => $definition) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $this->settings->set(
                $this->storageKey($key),
                $this->castStoredValue($values[$key], $definition['type']),
                $companyId,
                null,
                $definition['type'],
            );
        }

        Cache::forget($this->runtimeCacheKey($companyId));
    }

    public function applyRuntimeOverrides(?int $companyId = null): void
    {
        $companyId ??= tenant()->companyId();

        if (! $companyId) {
            return;
        }

        $cacheKey = $this->runtimeCacheKey($companyId);

        $merged = Cache::remember($cacheKey, 300, fn () => $this->effectiveConfig($companyId));

        config(['printing_intelligence' => array_merge(config('printing_intelligence', []), $merged)]);
    }

    protected function storageKey(string $key): string
    {
        return 'printing_intelligence.'.$key;
    }

    protected function runtimeCacheKey(int $companyId): string
    {
        return 'printing_intelligence.runtime.'.$companyId;
    }

    protected function castStoredValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    public function __construct(
        protected SystemSettingsService $settings,
    ) {}
}
