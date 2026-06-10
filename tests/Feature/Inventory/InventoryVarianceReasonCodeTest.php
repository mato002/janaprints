<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryVarianceReasonCategory;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryVarianceReasonCode;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryVarianceReasonCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_reason_code_crud_works(): void
    {
        [$company, $branch, $user] = $this->managerContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.inventory.variance-reason-codes.store'), [
            'code' => 'TEST-CODE',
            'name' => 'Test variance reason',
            'category' => InventoryVarianceReasonCategory::CountingError->value,
            'requires_comment' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.inventory.variance-reason-codes.index'));

        $code = InventoryVarianceReasonCode::query()
            ->where('company_id', $company->id)
            ->where('code', 'TEST-CODE')
            ->first();

        $this->assertNotNull($code);

        $this->actingAs($user)->put(route('admin.inventory.variance-reason-codes.update', $code), [
            'code' => 'TEST-CODE',
            'name' => 'Updated reason',
            'category' => InventoryVarianceReasonCategory::PaperDamage->value,
            'requires_comment' => 0,
            'is_active' => 1,
        ])->assertRedirect(route('admin.inventory.variance-reason-codes.index'));

        $this->assertSame('Updated reason', $code->fresh()->name);
    }

    public function test_inactive_reason_codes_not_listed_as_active_options(): void
    {
        [$company, $branch, $user] = $this->managerContext();

        $inactive = InventoryVarianceReasonCode::query()->create([
            'company_id' => $company->id,
            'code' => 'INACTIVE',
            'name' => 'Inactive reason',
            'category' => InventoryVarianceReasonCategory::Other,
            'requires_comment' => true,
            'is_active' => false,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.variance-reason-codes.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('INACTIVE', false);

        $this->actingAs($user)
            ->get(route('admin.inventory.variance-reason-codes.index', ['status' => 'active']))
            ->assertOk()
            ->assertDontSee('Inactive reason', false);
    }

    public function test_inactive_reason_code_cannot_be_used_on_worksheet_update(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->countContext();

        $inactive = InventoryVarianceReasonCode::query()->create([
            'company_id' => $company->id,
            'code' => 'OFF',
            'name' => 'Off reason',
            'category' => InventoryVarianceReasonCategory::Other,
            'requires_comment' => false,
            'is_active' => false,
        ]);

        $count = \App\Support\Inventory\StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: \App\Enums\StockCountType::Partial,
            countDate: now()->toDateString(),
            userId: $user->id,
            itemIds: [$item->id],
        );

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $line = $count->items()->first();

        $this->actingAs($user)->from(route('admin.inventory.stock-counts.worksheet', $count))
            ->put(route('admin.inventory.stock-counts.worksheet.update', $count), [
                'items' => [[
                    'inventory_item_id' => $item->id,
                    'counted_quantity' => 5,
                    'inventory_variance_reason_code_id' => $inactive->id,
                ]],
            ])->assertSessionHasErrors('inventory_variance_reason_code_id');
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function managerContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'inventory.variance-reasons.view', 'inventory.variance-reasons.manage',
        ]);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: \App\Models\Inventory\InventoryItem, 4: \App\Models\Inventory\Warehouse}
     */
    protected function countContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'inventory.count.view', 'inventory.count.create', 'inventory.count.edit',
            'inventory.variance-reasons.view',
        ]);
        $user->assignRole('Storekeeper');

        $this->seed(\Database\Seeders\InventoryFoundationSeeder::class);

        $warehouse = \App\Models\Inventory\Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();

        $category = \App\Models\Inventory\InventoryCategory::query()
            ->where('company_id', $company->id)
            ->firstOrFail();

        $unit = \App\Models\Inventory\UnitOfMeasure::query()
            ->where('company_id', $company->id)
            ->firstOrFail();

        $item = \App\Models\Inventory\InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VRC-'.uniqid(),
            'standard_cost' => 10,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }
}
