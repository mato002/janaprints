<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Enums\PrintInkType;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\Sales\Quotation;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintingIntelligenceGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_gateway_returns_trusted_context_payloads(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $gateway = app(PrintingIntelligenceGateway::class);

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'item_name' => 'Vinyl Banner Material',
        ]);

        $ink = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'UV Cyan',
            'ink_type' => PrintInkType::Uv,
            'cartridge_cost' => 3000,
            'estimated_ml' => 500,
            'active' => true,
        ]);

        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'GW-'.uniqid(),
            'asset_name' => 'Gateway Press',
            'acquisition_cost' => 50000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);
        $machine = MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'GW-M-'.uniqid(),
            'machine_type' => 'large_format',
            'cost_per_hour' => 900,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $material = $gateway->materialContext($item->id);
        $this->assertTrue($material['found']);

        $machineCtx = $gateway->machineContext($machine->id, 1);
        $this->assertNotNull($machineCtx);
        $this->assertEqualsWithDelta(900, $machineCtx['cost_per_hour'], 0.01);

        $inkCtx = $gateway->inkContext($ink->id);
        $this->assertNotNull($inkCtx);
        $this->assertSame('uv', $inkCtx['ink_type']);

        $inventory = $gateway->inventoryContext($item->id);
        $this->assertSame('inventory_costing', $inventory['source']);

        $quoteCtx = $gateway->quotationContext($quotation->id);
        $this->assertNotNull($quoteCtx);
        $this->assertNull($quoteCtx['estimated_total_cost']);
        $this->assertTrue($quoteCtx['intelligence_ready']);

        $overview = $gateway->overviewMetrics($company->id, $branch->id);
        $this->assertArrayHasKey('materials_tracked', $overview);
        $this->assertGreaterThanOrEqual(1, $overview['ink_profiles']);
    }

    public function test_production_reality_returns_null_for_missing_job(): void
    {
        $this->assertNull(app(PrintingIntelligenceGateway::class)->productionReality(999999));
    }
}
