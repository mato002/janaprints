<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Accounting\InventoryAccountingPostingService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreDeskOperatorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_storekeeper_can_open_catalogue_and_reorder_alerts_from_desk(): void
    {
        [$company, $branch, $user] = $this->storeContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $catalogueUrl = route('admin.store.desk.catalogue');
        $alertsUrl = route('admin.store.desk.reorder-alerts');

        $this->actingAs($user)
            ->get(route('admin.store.desk'))
            ->assertOk()
            ->assertSee('data-erp-modal-open', false)
            ->assertSee('/admin/store/desk/catalogue', false)
            ->assertSee('/admin/store/desk/reorder-alerts', false);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get($catalogueUrl)
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Catalogue'), false);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get($alertsUrl)
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Reorder alerts'), false);

        $redirect = $this->actingAs($user)
            ->get(route('admin.inventory.items.index'))
            ->headers
            ->get('Location');

        $this->assertNotNull($redirect);
        $this->assertStringContainsString('desk=1', (string) $redirect);
        $this->assertStringNotContainsString('/admin/store/desk', (string) $redirect);
    }

    public function test_storekeeper_lands_on_store_desk_with_pending_drafts(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->storeContext();

        $receipt = $this->draftReceipt($company, $branch, $user, $item, $warehouse);
        $issue = $this->draftIssue($company, $branch, $user, $item, $warehouse);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.store.desk'))
            ->assertOk()
            ->assertSee(__('Finish these drafts'), false)
            ->assertSee($receipt->receipt_number, false)
            ->assertSee($issue->issue_number, false)
            ->assertSee(__('Post to stock'), false);
    }

    public function test_storekeeper_can_receive_and_post_from_desk_in_one_step(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->storeContext();
        $receipt = $this->draftReceipt($company, $branch, $user, $item, $warehouse, 25);

        $session = [
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ];

        $this->actingAs($user)
            ->withSession($session)
            ->get(route('admin.inventory.receipts.create', ['from' => 'store-desk']))
            ->assertOk()
            ->assertSee('name="from"', false)
            ->assertSee('value="store-desk"', false);

        $this->actingAs($user)
            ->withSession($session)
            ->post(route('admin.inventory.receipts.post', $receipt), [
                'from' => 'store-desk',
            ])
            ->assertRedirect(route('admin.store.desk'));

        $this->assertSame(InventoryDocumentStatus::Posted, $receipt->fresh()->status);
        $this->assertTrue(
            InventoryMovement::query()
                ->where('inventory_item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->exists()
        );
    }

    public function test_storekeeper_can_open_receipt_review_modal_from_desk(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->storeContext();
        $receipt = $this->draftReceipt($company, $branch, $user, $item, $warehouse);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $modalUrl = route('admin.inventory.receipts.show', [$receipt, 'from' => 'store-desk']);

        $this->actingAs($user)
            ->get(route('admin.store.desk'))
            ->assertOk()
            ->assertSee('data-erp-modal-open', false)
            ->assertSee('from=store-desk', false);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get($modalUrl)
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Post to stock'), false);
    }

    public function test_storekeeper_can_post_pending_draft_from_desk(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->storeContext();
        $receipt = $this->draftReceipt($company, $branch, $user, $item, $warehouse, 40);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.receipts.post', $receipt), [
                'from' => 'store-desk',
            ])
            ->assertRedirect(route('admin.store.desk'));

        $this->assertSame(InventoryDocumentStatus::Posted, $receipt->fresh()->status);
        $this->assertGreaterThan(0, InventoryMovement::query()->count());
    }

    public function test_storekeeper_can_issue_and_post_after_receipt(): void
    {
        $this->mock(InventoryAccountingPostingService::class, function ($mock): void {
            $mock->shouldReceive('postStockReceipt')->andReturn(null);
            $mock->shouldReceive('postStockIssue')->andReturn(null);
        });

        [$company, $branch, $user, $item, $warehouse] = $this->storeContext();
        $receipt = $this->draftReceipt($company, $branch, $user, $item, $warehouse, 50);
        $issue = $this->draftIssue($company, $branch, $user, $item, $warehouse);

        $session = [
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ];

        $this->actingAs($user)
            ->withSession($session)
            ->post(route('admin.inventory.receipts.post', $receipt), ['from' => 'store-desk'])
            ->assertRedirect(route('admin.store.desk'));

        $this->actingAs($user)
            ->withSession($session)
            ->post(route('admin.inventory.issues.post', $issue), ['from' => 'store-desk'])
            ->assertRedirect(route('admin.store.desk'));

        $this->assertSame(InventoryDocumentStatus::Posted, $receipt->fresh()->status);
        $this->assertSame(InventoryDocumentStatus::Posted, $issue->fresh()->status);
    }

    public function test_storekeeper_issue_create_form_includes_desk_return_marker(): void
    {
        [$company, $branch, $user] = $this->storeContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.issues.create', ['from' => 'store-desk']))
            ->assertOk()
            ->assertSee('name="from"', false)
            ->assertSee('value="store-desk"', false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function storeContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'catalogue.view',
            'inventory.reorder.view',
        ]);
        $user->assignRole('Storekeeper');

        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('is_virtual', false)
            ->firstOrFail();

        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'STORE-DESK-001',
            'reorder_level' => 5,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function draftReceipt(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        Warehouse $warehouse,
        float $qty = 12,
    ): StockReceipt {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'DRAFT-RCPT-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $qty,
            'unit_cost' => 10,
        ]);

        return $receipt;
    }

    protected function draftIssue(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        Warehouse $warehouse,
    ): StockIssue {
        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'DRAFT-ISSUE-'.uniqid(),
            'destination' => StockIssueDestination::InternalUse,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'unit_cost' => 10,
        ]);

        return $issue;
    }
}
