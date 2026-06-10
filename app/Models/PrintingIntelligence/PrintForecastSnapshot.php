<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\ForecastModel;
use App\Enums\ForecastPeriodType;
use App\Enums\ForecastType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PrintForecastSnapshot extends Model
{
    use BelongsToTenant;

    protected $table = 'print_forecast_snapshots';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'forecast_type',
        'period_type',
        'forecast_period_start',
        'forecast_period_end',
        'historical_periods_used',
        'forecast_value',
        'lower_bound',
        'upper_bound',
        'confidence_score',
        'forecast_model',
        'forecast_version',
        'metadata',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'forecast_type' => ForecastType::class,
            'period_type' => ForecastPeriodType::class,
            'forecast_model' => ForecastModel::class,
            'forecast_period_start' => 'date',
            'forecast_period_end' => 'date',
            'forecast_value' => 'decimal:2',
            'lower_bound' => 'decimal:2',
            'upper_bound' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'metadata' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
