<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ForecastModel;
use App\Services\PrintingIntelligence\ExecutiveForecastingService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveForecastingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_project_uses_weighted_average_by_default(): void
    {
        $result = app(ExecutiveForecastingService::class)->project([100, 200, 300]);

        $this->assertSame(ForecastModel::WeightedAverage, $result['forecast_model']);
        $this->assertGreaterThan(200, (float) $result['forecast_value']);
        $this->assertGreaterThan(0, (float) $result['confidence_score']);
    }

    public function test_trend_projection_forecasts_forward(): void
    {
        $result = app(ExecutiveForecastingService::class)->project([100, 150, 200], ForecastModel::TrendProjection);

        $this->assertGreaterThan(200, (float) $result['forecast_value']);
    }
}
