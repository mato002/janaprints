<?php

namespace Tests\Feature\Reports;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_operational_registers_hub_renders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery()))
            ->assertOk()
            ->assertSee(__('Operational Registers'), false)
            ->assertSee(__('Daily Sales Register'), false)
            ->assertSee(__('Sales today'), false)
            ->assertSee(__('Read-only register generated from live ERP data'), false);
    }

    #[DataProvider('departmentRegisterProvider')]
    public function test_department_register_renders(string $register, string $label): void
    {
        [$company, $branch, $user, $workCenter] = $this->productionContext($register);

        $this->queueJob($company, $branch, $user, $workCenter, ProductionType::Digital);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery([
                'register' => $register,
                'from_date' => now()->subMonth()->toDateString(),
                'to_date' => now()->toDateString(),
            ])))
            ->assertOk()
            ->assertSee($label, false);
    }

    public static function departmentRegisterProvider(): array
    {
        return [
            'digital' => ['digital', 'Digital Department Register'],
            'offset' => ['offset', 'Offset Department Register'],
            'outsource' => ['outsource', 'Outsourced Jobs Register'],
            'large_format' => ['large_format', 'Large Format Register'],
            'finishing' => ['finishing', 'Finishing Register'],
        ];
    }

    public function test_daily_sales_register_includes_sales_order(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'order_date' => today(),
            'total_amount' => 1500,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery([
                'register' => 'daily_sales',
                'preset' => 'today',
            ])))
            ->assertOk()
            ->assertSee($order->order_number, false)
            ->assertSee($customer->company_name, false);
    }

    public function test_daily_sales_register_sorts_most_recent_first(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $older = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'order_date' => today(),
            'total_amount' => 100,
        ]);

        $newer = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'order_date' => today(),
            'total_amount' => 200,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery([
                'register' => 'daily_sales',
                'preset' => 'today',
            ])));

        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), $older->order_number),
            strpos($response->getContent(), $newer->order_number),
        );
    }

    public function test_machine_utilisation_register_renders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery([
                'register' => 'machine_utilisation',
            ])))
            ->assertOk()
            ->assertSee(__('Machine Utilisation Register'), false);
    }

    public function test_department_performance_register_renders(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery([
                'register' => 'department_performance',
            ])))
            ->assertOk()
            ->assertSee(__('Department Performance Register'), false)
            ->assertSee(__('Digital'), false);
    }

    public function test_export_respects_register_and_filters(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'order_date' => today(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.reports.operational-registers.export', [
                'register' => 'daily_sales',
                'preset' => 'today',
                'format' => 'csv',
            ]));

        $response->assertOk();
        $this->assertStringContainsString($order->order_number, $response->streamedContent());
    }

    public function test_kpi_drill_down_links_present(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'production.queue.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery()))
            ->assertOk()
            ->assertSee(route('admin.production.queue.index'), false);
    }

    public function test_forbidden_without_permission(): void
    {
        [$company, $branch] = $this->tenantUser([]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.operational-registers', $this->embeddedQuery()))
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        if ($permissions !== []) {
            $role = Role::findByName('Production', 'web');
            $role->syncPermissions($permissions);
            $user->assignRole('Production');
        }

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: WorkCenter}
     */
    protected function productionContext(string $register): array
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'production.queue.view']);
        $this->seed(ProductionFoundationSeeder::class);

        $code = match ($register) {
            'offset' => 'OFFSET',
            'large_format' => 'LARGE_FORMAT',
            'finishing' => 'FINISHING',
            default => 'DIGITAL',
        };

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->firstOrFail();

        return [$company, $branch, $user, $workCenter];
    }

    protected function queueJob(
        Company $company,
        Branch $branch,
        User $user,
        WorkCenter $workCenter,
        ProductionType $type,
    ): ProductionJobCard {
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => $type,
            'created_by' => $user->id,
            'status' => ProductionJobCardStatus::Draft,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Queued,
        ]);

        return $jobCard;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function embeddedQuery(array $extra = []): array
    {
        return array_merge(['embedded' => '1'], $extra);
    }
}
