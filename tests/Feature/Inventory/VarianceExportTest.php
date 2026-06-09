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
use App\Support\Inventory\StockCountService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VarianceExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_csv_export_returns_download(): void
    {
        [$company, $branch, $user] = $this->contextWithVariance();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.variances.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_excel_export_returns_download(): void
    {
        [$company, $branch, $user] = $this->contextWithVariance();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.inventory.variances.export-excel'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->assertStringContainsString('Expected Qty', $response->streamedContent());
    }

    public function test_pdf_export_returns_full_report_document(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->contextWithVariance();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.inventory.variances.export-pdf'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Inventory Variance Report', $content);
        $this->assertStringContainsString('Variance summary', $content);
        $this->assertStringContainsString('Detailed variances', $content);
        $this->assertStringContainsString($item->item_name, $content);
        $this->assertStringContainsString($item->sku, $content);
        $this->assertStringContainsString($warehouse->name, $content);
        $this->assertStringContainsString('Positive variance', $content);
        $this->assertStringContainsString('Negative variance', $content);
        $this->assertStringContainsString('Net variance', $content);
    }

    public function test_exports_do_not_mutate_stock(): void
    {
        [$company, $branch, $user] = $this->contextWithVariance();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $movementsBefore = InventoryMovement::query()->count();

        $this->actingAs($user)->get(route('admin.inventory.variances.export'))->assertOk();
        $this->actingAs($user)->get(route('admin.inventory.variances.export-excel'))->assertOk();
        $this->actingAs($user)->get(route('admin.inventory.variances.export-pdf'))->assertOk();

        $this->assertEquals($movementsBefore, InventoryMovement::query()->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function contextWithVariance(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.variance.view', 'inventory.variance.export']);
        $user->assignRole('Storekeeper');
        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VARX-'.uniqid(),
            'item_name' => 'Variance Export Item',
        ]);

        NumberingSequence::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => $branch->id, 'document_type' => DocumentType::StockCount->value],
            ['format_template' => 'SC-{number}', 'next_number' => 1, 'padding' => 5, 'include_year' => false, 'include_branch_code' => false],
        );

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

        return [$company, $branch, $user, $item, $warehouse];
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
