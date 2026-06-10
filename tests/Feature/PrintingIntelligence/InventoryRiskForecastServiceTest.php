<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\InventoryRiskLevel;
use App\Enums\InventoryVelocityClass;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Services\PrintingIntelligence\InventoryRiskForecastService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryRiskForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_forecasts_inventory_risk_categories(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('company_id', $company->id)->firstOrFail();
        $warehouse = \App\Models\Inventory\Warehouse::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAIN'],
            ['branch_id' => $branch->id, 'name' => 'Main Warehouse', 'is_active' => true],
        );

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'item_name' => 'CMYK Ink Cartridge',
            'sku' => 'INK-CMYK-1',
        ]);

        InventoryVelocitySnapshot::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'stock_role' => 'raw_material',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => today()->toDateString(),
            'movement_window_days' => (int) config('inventory_intelligence.default_snapshot_window', 30),
            'opening_balance' => 10,
            'closing_balance' => 5,
            'total_out_quantity' => 5,
            'velocity_class' => InventoryVelocityClass::FastMoving,
            'risk_level' => InventoryRiskLevel::Critical,
            'days_to_depletion' => 6,
            'generated_at' => now(),
        ]);

        $this->assertSame(1, InventoryVelocitySnapshot::query()->where('company_id', $company->id)->count());

        $result = app(InventoryRiskForecastService::class)->forecast(['company_id' => $company->id]);

        $this->assertTrue($result['read_only']);
        $this->assertNotEmpty($result['categories']);
        $inkCategory = collect($result['categories'])->firstWhere('category', 'ink');
        $this->assertSame('critical', $inkCategory['risk_class'] ?? null);
        $this->assertSame(6.0, (float) ($inkCategory['days_to_risk'] ?? 0));
    }
}
