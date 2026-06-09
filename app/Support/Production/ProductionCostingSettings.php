<?php

namespace App\Support\Production;

use App\Models\Company;

class ProductionCostingSettings
{
    public const DEFAULT_VARIANCE_TOLERANCE_PERCENT = 10.0;

    public const DEFAULT_OVERHEAD_PERCENT = 5.0;

    public const DEFAULT_LABOR_HOURS_PER_MONTH = 176;

    public const DEFAULT_MACHINE_HOURLY_RATE = 150.0;

    /**
     * @return array{
     *     overhead_method: string,
     *     overhead_percent: float,
     *     overhead_flat_amount: float,
     *     variance_tolerance_percent: float,
     *     default_machine_hourly_rate: float,
     *     labor_hours_per_month: float,
     * }
     */
    public static function forCompany(Company $company): array
    {
        $settings = $company->settings_json['production_costing'] ?? [];

        return [
            'overhead_method' => $settings['overhead_method'] ?? 'percentage',
            'overhead_percent' => (float) ($settings['overhead_percent'] ?? self::DEFAULT_OVERHEAD_PERCENT),
            'overhead_flat_amount' => (float) ($settings['overhead_flat_amount'] ?? 0),
            'variance_tolerance_percent' => (float) ($settings['variance_tolerance_percent'] ?? self::DEFAULT_VARIANCE_TOLERANCE_PERCENT),
            'default_machine_hourly_rate' => (float) ($settings['default_machine_hourly_rate'] ?? self::DEFAULT_MACHINE_HOURLY_RATE),
            'labor_hours_per_month' => (float) ($settings['labor_hours_per_month'] ?? self::DEFAULT_LABOR_HOURS_PER_MONTH),
        ];
    }
}
