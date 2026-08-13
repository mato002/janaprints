<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryStockRole;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Support\Production\MaterialRequirementsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialsWorkflowChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_workflow_starts_at_link_product_when_sales_order_has_no_finished_item(): void
    {
        [$user, $jobCard] = $this->jobWithoutFinishedProduct();

        $workflow = app(MaterialRequirementsService::class)->workflowChecklist($jobCard);

        $this->assertFalse($workflow['has_finished_product']);
        $this->assertSame('link_product', $workflow['current_key']);
        $this->assertFalse($workflow['can_generate']);
        $this->assertNotNull($workflow['blocker']);
    }

    public function test_materials_tab_shows_link_product_step_instead_of_dead_generate(): void
    {
        [$user, $jobCard] = $this->jobWithoutFinishedProduct([
            'production.view',
            'production.materials.generate',
            'production.edit',
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']))
            ->assertOk()
            ->assertSee(__('Use the highlighted step to add a BOM or generate requirements on this job.'), false)
            ->assertSee(__('Link finished product'), false)
            ->assertSee(__('Link product'), false)
            ->assertDontSee('name="warehouse_id"', false);
    }

    public function test_link_finished_product_advances_workflow_and_updates_order(): void
    {
        [$user, $jobCard, $finished] = $this->jobWithoutFinishedProduct([
            'production.view',
            'production.materials.generate',
            'production.edit',
        ], withFinishedCatalog: true);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.finished-product', $jobCard), [
                'inventory_item_id' => $finished->id,
            ])
            ->assertRedirect();

        $jobCard->refresh();
        $this->assertSame($finished->id, $jobCard->inventory_item_id);
        $this->assertSame($finished->id, $jobCard->salesOrder->fresh()->inventory_item_id);

        $workflow = app(MaterialRequirementsService::class)->workflowChecklist($jobCard->fresh());
        $this->assertTrue($workflow['has_finished_product']);
        $this->assertSame('bom', $workflow['current_key']);
    }

    public function test_in_production_job_without_materials_shows_blocker_not_clear(): void
    {
        [$user, $jobCard] = $this->jobWithoutFinishedProduct([
            'production.view',
            'production.materials.generate',
        ], status: ProductionJobCardStatus::InProduction);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']))
            ->assertOk()
            ->assertSee(__('No finished product linked'), false)
            ->assertDontSee(__('No blockers'), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: User, 1: ProductionJobCard, 2?: InventoryItem}
     */
    protected function jobWithoutFinishedProduct(
        array $permissions = ['production.view', 'production.materials.generate'],
        bool $withFinishedCatalog = false,
        ProductionJobCardStatus $status = ProductionJobCardStatus::Draft,
    ): array {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::create(['name' => 'Materials Workflow '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'inventory_item_id' => null,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'inventory_item_id' => null,
            'item_name' => 'Business Cards',
            'quantity' => 2000,
            'unit_price' => 10,
            'line_total' => 20000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'inventory_item_id' => null,
            'status' => $status,
            'created_by' => $user->id,
        ]);

        $finished = null;
        if ($withFinishedCatalog) {
            $finished = InventoryItem::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'stock_role' => InventoryStockRole::FinishedGood,
                'is_active' => true,
                'item_name' => 'Business Cards FG',
                'sku' => 'FG-BC-001',
            ]);
        }

        return $finished
            ? [$user, $jobCard, $finished]
            : [$user, $jobCard];
    }
}
