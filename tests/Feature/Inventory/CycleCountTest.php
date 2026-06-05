<?php

namespace Tests\Feature\Inventory;

use App\Enums\CycleCountFrequency;
use App\Enums\DocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\CycleCountSchedule;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Inventory\CycleCountService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CycleCountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_create_schedule_and_generate_stock_count(): void
    {
        [$company, $branch, $user, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);

        $schedule = CycleCountService::createSchedule(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            frequency: CycleCountFrequency::Monthly->value,
            nextCountDate: now()->toDateString(),
            responsibleUserId: $user->id,
        );

        $count = CycleCountService::generateCount($schedule, $user->id);

        $this->assertDatabaseHas('stock_counts', [
            'id' => $count->id,
            'cycle_count_schedule_id' => $schedule->id,
            'warehouse_id' => $warehouse->id,
        ]);
    }

    public function test_overdue_schedule_visible(): void
    {
        [$company, $branch, $user, $warehouse] = $this->context();

        CycleCountSchedule::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'frequency' => CycleCountFrequency::Weekly,
            'next_count_date' => now()->subDays(3)->toDateString(),
            'responsible_user_id' => $user->id,
            'status' => 'active',
        ]);

        $overdue = CycleCountService::overdueSchedules($company->id, $branch->id);
        $this->assertCount(1, $overdue);
    }

    public function test_cycle_count_index_accessible(): void
    {
        [$company, $branch, $user] = $this->context();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.cycle-counts.index'))
            ->assertOk();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Warehouse}
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
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'inventory.cycle.view', 'inventory.cycle.manage',
            'inventory.count.view', 'inventory.count.create',
        ]);
        $user->assignRole('Storekeeper');
        $this->seed(InventoryFoundationSeeder::class);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();

        return [$company, $branch, $user, $warehouse];
    }

    protected function seedNumbering(Company $company, Branch $branch): void
    {
        NumberingSequence::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => $branch->id, 'document_type' => DocumentType::StockCount->value],
            ['format_template' => 'SC-{number}', 'next_number' => 1, 'padding' => 5, 'include_year' => false, 'include_branch_code' => false],
        );
    }
}
