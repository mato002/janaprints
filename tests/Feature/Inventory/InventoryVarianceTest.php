<?php

namespace Tests\Feature\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockCountType;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Inventory\InventoryVarianceService;
use App\Support\Inventory\StockCountService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryVarianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_variance_visible_after_counting(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );

        StockCountService::updateCountedQuantities($count, [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 18,
        ]], $user->id);

        $variances = InventoryVarianceService::query($company->id, $branch->id)->get();
        $this->assertCount(1, $variances);
        $this->assertEquals(-2, (float) $variances->first()->variance_quantity);
    }

    public function test_filters_work(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );
        StockCountService::updateCountedQuantities($count, [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 25,
        ]], $user->id);

        $positive = InventoryVarianceService::query($company->id, $branch->id, ['variance_type' => 'positive'])->get();
        $negative = InventoryVarianceService::query($company->id, $branch->id, ['variance_type' => 'negative'])->get();

        $this->assertCount(1, $positive);
        $this->assertCount(0, $negative);
    }

    public function test_export_route_works(): void
    {
        [$company, $branch, $user] = $this->context();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.variances.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_report_does_not_mutate_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 30);

        $movementsBefore = InventoryMovement::query()->count();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        $this->actingAs($user)->get(route('admin.inventory.variances.index'))->assertOk();
        InventoryVarianceService::query($company->id, $branch->id)->get();

        $this->assertEquals($movementsBefore, InventoryMovement::query()->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function context(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.variance.view']);
        $user->assignRole('Storekeeper');
        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VAR-'.uniqid(),
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function seedNumbering(Company $company, Branch $branch): void
    {
        NumberingSequence::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => $branch->id, 'document_type' => DocumentType::StockCount->value],
            ['format_template' => 'SC-{number}', 'next_number' => 1, 'padding' => 5, 'include_year' => false, 'include_branch_code' => false],
        );
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): void
    {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create(['inventory_item_id' => $item->id, 'quantity' => $qty, 'unit_cost' => 10]);
        StockReceiptService::post($receipt, $user->id);
    }
}
