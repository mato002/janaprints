<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\QuotationPricingService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QuotationPricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->service = app(QuotationPricingService::class);
        config(['printing_intelligence.quotation_formula_version' => 'PI5-V1']);
    }

    public function test_calculates_total_and_selling_prices(): void
    {
        $result = $this->service->price([
            'material_cost' => 200,
            'ink_cost' => 50,
            'machine_cost' => 300,
            'labour_cost' => 150,
            'electricity_cost' => 40,
            'overhead_cost' => 45,
            'wastage_cost' => 12.5,
        ], 20, 35);

        $this->assertEqualsWithDelta(797.5, $result['estimated_total_cost'], 0.01);
        $this->assertGreaterThan($result['estimated_total_cost'], $result['minimum_selling_price']);
        $this->assertGreaterThan($result['minimum_selling_price'], $result['recommended_selling_price']);
        $this->assertSame('PI5-V1', $result['formula_version']);
    }

    public function test_applies_rounding_rule(): void
    {
        config(['printing_intelligence.rounding_rule' => 'nearest_10']);

        $result = $this->service->price([
            'material_cost' => 100,
            'ink_cost' => 0,
            'machine_cost' => 0,
            'labour_cost' => 0,
            'electricity_cost' => 0,
            'overhead_cost' => 0,
            'wastage_cost' => 0,
        ], 0, 33.33);

        $this->assertSame(0.0, fmod($result['recommended_selling_price'], 10));
    }

    public function test_calculates_expected_margin(): void
    {
        $result = $this->service->price([
            'material_cost' => 650,
            'ink_cost' => 0,
            'machine_cost' => 0,
            'labour_cost' => 0,
            'electricity_cost' => 0,
            'overhead_cost' => 0,
            'wastage_cost' => 0,
        ], 20, 35);

        $this->assertNotNull($result['expected_margin_percent']);
        $this->assertGreaterThan(30, $result['expected_margin_percent']);
    }
}
