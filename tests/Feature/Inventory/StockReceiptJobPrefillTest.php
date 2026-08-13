<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryStockRole;
use App\Enums\MaterialRequirementStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\User;
use App\Support\Production\MaterialRequirementsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockReceiptJobPrefillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_stock_receipt_create_prefills_job_shortages(): void
    {
        [$user, $jobCard] = $this->jobWithShortages();

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.create', [
                'job_card_id' => $jobCard->getRouteKey(),
            ]))
            ->assertOk()
            ->assertSee('Lines are filled from shortages on DEMO-CC-PREFILL', false)
            ->assertSee('name="job_card_id"', false)
            ->assertSee('value="'.$jobCard->getRouteKey().'"', false)
            ->assertSee('\\u0022quantity\\u0022:\\u00224240\\u0022', false)
            ->assertSee('\\u0022unit_cost\\u0022:\\u002212.50\\u0022', false)
            ->assertSee('\\u0022quantity\\u0022:\\u0022166.4\\u0022', false)
            ->assertSee('\\u0022unit_cost\\u0022:\\u002220.00\\u0022', false)
            ->assertSee('RAW-PAPER-350', false)
            ->assertSee('RAW-INK-CMYK', false);
    }

    public function test_stock_receipt_create_stays_empty_without_job(): void
    {
        [$user] = $this->jobWithShortages();

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.create'))
            ->assertOk()
            ->assertDontSee('Lines are filled from shortages', false)
            ->assertDontSee('\\u0022quantity\\u0022:\\u00224240\\u0022', false);
    }

    public function test_materials_receive_stock_points_at_job_prefill(): void
    {
        [$user, $jobCard] = $this->jobWithShortages();

        $expected = route('admin.inventory.receipts.create', [
            'job_card_id' => $jobCard->getRouteKey(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', [
                'jobCard' => $jobCard,
                'tab' => 'materials',
            ]))
            ->assertOk()
            ->assertSee($expected, false)
            ->assertSee('data-erp-modal-open', false);
    }

    public function test_service_groups_duplicate_shortfall_items(): void
    {
        [, $jobCard, $paper, , $warehouse] = $this->jobWithShortages();

        ProductionMaterialRequirement::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'required_quantity' => 10,
            'unit_cost' => 0,
            'status' => MaterialRequirementStatus::Planned,
            'generated_by' => $jobCard->created_by,
            'generated_at' => now(),
        ]);

        $prefill = app(MaterialRequirementsService::class)->stockReceiptPrefill($jobCard->fresh());

        $paperLine = collect($prefill['lines'])->firstWhere('inventory_item_id', (string) $paper->id);

        $this->assertNotNull($paperLine);
        $this->assertSame('4250', $paperLine['quantity']);
        $this->assertSame($warehouse->id, $prefill['warehouse_id']);
    }

    /**
     * @return array{0: User, 1: ProductionJobCard, 2: InventoryItem, 3: InventoryItem, 4: Warehouse}
     */
    protected function jobWithShortages(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::create(['name' => 'Receipt Prefill '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions([
            'production.view',
            'inventory.view',
            'inventory.receive',
        ]);
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $warehouse = Warehouse::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'is_active' => true,
            'is_virtual' => false,
            'name' => 'Raw Materials Store',
        ]);

        $paper = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'is_active' => true,
            'item_name' => 'Art Paper 350gsm',
            'sku' => 'RAW-PAPER-350',
            'standard_cost' => 8,
        ]);
        $ink = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'is_active' => true,
            'item_name' => 'CMYK Process Ink',
            'sku' => 'RAW-INK-CMYK',
            'standard_cost' => 20,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::InProduction,
            'created_by' => $user->id,
            'job_card_number' => 'DEMO-CC-PREFILL',
        ]);

        ProductionMaterialRequirement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'required_quantity' => 4240,
            'unit_cost' => 12.5,
            'status' => MaterialRequirementStatus::Planned,
            'generated_by' => $user->id,
            'generated_at' => now(),
        ]);
        ProductionMaterialRequirement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $ink->id,
            'warehouse_id' => $warehouse->id,
            'required_quantity' => 166.4,
            'unit_cost' => 0,
            'status' => MaterialRequirementStatus::Planned,
            'generated_by' => $user->id,
            'generated_at' => now(),
        ]);

        return [$user, $jobCard, $paper, $ink, $warehouse];
    }
}
