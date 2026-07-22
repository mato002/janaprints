<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\ProductionSpecificationService;
use App\Support\TenantContext;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    #[DataProvider('commandCentreProvider')]
    public function test_department_command_centre_renders(string $department, string $titleFragment): void
    {
        [$company, $branch, $user, $workCenter] = $this->commandCentreContext($department);

        $job = $this->queueJob(
            $company,
            $branch,
            $user,
            $workCenter,
            $this->productionTypeFor($department),
            $department === 'outsource',
        );

        $this->actingAs($user)
            ->getDepartmentQueue($department)
            ->assertOk()
            ->assertSee($titleFragment, false)
            ->assertSee($job->job_card_number, false)
            ->assertSee(__('Department operational register'), false)
            ->assertSee(__('Waiting jobs'), false)
            ->assertSee(__('Visible jobs'), false);
    }

    public static function commandCentreProvider(): array
    {
        return [
            'digital' => ['digital', 'Digital Command Centre'],
            'offset' => ['offset', 'Offset Command Centre'],
            'outsource' => ['outsource', 'Outsource Command Centre'],
            'large_format' => ['large_format', 'Large Format Command Centre'],
            'finishing' => ['finishing', 'Finishing Command Centre'],
        ];
    }

    public function test_offset_command_centre_shows_register_columns(): void
    {
        [$company, $branch, $user, $workCenter] = $this->commandCentreContext('offset');

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $item = \App\Models\Sales\SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'item_name' => 'Brochure',
            'quantity' => 500,
            'unit_price' => 12.5,
            'line_total' => 6250,
            'sort_order' => 1,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'production_type' => ProductionType::Offset,
            'created_by' => $user->id,
        ]);

        app(ProductionSpecificationService::class)->createForSalesOrderItem($item, [
            'production_type' => ProductionType::Offset->value,
            'product_description' => 'Annual report',
            'quantity' => 500,
            'finished_size' => '210x297',
            'lamination' => true,
        ], $user)->update(['production_job_card_id' => $jobCard->id]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => 1,
            'status' => ProductionQueueStatus::Queued,
        ]);

        $this->actingAs($user)
            ->getDepartmentQueue('offset')
            ->assertOk()
            ->assertSee(__('Type'), false)
            ->assertSee(__('Ink colour'), false)
            ->assertSee(__('Status'), false)
            ->assertSee('Annual report', false);
    }

    public function test_export_respects_department_scope(): void
    {
        [$company, $branch, $user, $digitalCenter] = $this->commandCentreContext('digital');

        $offsetCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', 'OFFSET')
            ->firstOrFail();

        $digitalJob = $this->queueJob($company, $branch, $user, $digitalCenter, ProductionType::Digital);
        $offsetJob = $this->queueJob($company, $branch, $user, $offsetCenter, ProductionType::Offset);

        $response = $this->actingAs($user)
            ->get(route('admin.production.queue.export', [
                'department' => 'digital',
                'format' => 'csv',
            ]));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($digitalJob->job_card_number, $content);
        $this->assertStringNotContainsString($offsetJob->job_card_number, $content);
    }

    public function test_export_respects_active_filters(): void
    {
        [$company, $branch, $user, $workCenter] = $this->commandCentreContext('digital');

        $overdueJob = $this->queueJob($company, $branch, $user, $workCenter, ProductionType::Digital);
        $overdueJob->update(['required_date' => now()->subDay()]);

        $futureJob = $this->queueJob($company, $branch, $user, $workCenter, ProductionType::Digital);
        $futureJob->update(['required_date' => now()->addWeek()]);

        $response = $this->actingAs($user)
            ->get(route('admin.production.queue.export', [
                'department' => 'digital',
                'format' => 'csv',
                'due' => 'overdue',
            ]));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($overdueJob->job_card_number, $content);
        $this->assertStringNotContainsString($futureJob->job_card_number, $content);
    }

    public function test_job_card_links_to_job360(): void
    {
        [$company, $branch, $user, $workCenter] = $this->commandCentreContext('digital');
        $job = $this->queueJob($company, $branch, $user, $workCenter, ProductionType::Digital);

        $this->actingAs($user)
            ->getDepartmentQueue('digital')
            ->assertOk()
            ->assertSee(route('admin.production.job-cards.show', $job), false);
    }

    public function test_command_centre_forbidden_without_permission(): void
    {
        [$company, $branch] = array_slice($this->commandCentreContext('digital'), 0, 2);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->getDepartmentQueue('digital')
            ->assertForbidden();
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getDepartmentQueue(string $department)
    {
        return $this->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.production.queue.department', $department).'?embedded=1');
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: WorkCenter}
     */
    protected function commandCentreContext(string $department): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.queue.view', 'production.view', 'production.work-centers.view']);
        $user->assignRole('Production');

        $this->seed(ProductionFoundationSeeder::class);

        $code = match ($department) {
            'offset' => 'OFFSET',
            'large_format' => 'LARGE_FORMAT',
            'finishing' => 'FINISHING',
            default => 'DIGITAL',
        };

        $workCenter = WorkCenter::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        return [$company, $branch, $user, $workCenter];
    }

    protected function productionTypeFor(string $department): ProductionType
    {
        return match ($department) {
            'offset' => ProductionType::Offset,
            'large_format' => ProductionType::LargeFormat,
            'finishing' => ProductionType::Finishing,
            default => ProductionType::Digital,
        };
    }

    protected function queueJob(
        Company $company,
        Branch $branch,
        User $user,
        WorkCenter $workCenter,
        ProductionType $type,
        bool $outsourced = false,
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
            'status' => $outsourced ? ProductionJobCardStatus::Outsourced : ProductionJobCardStatus::Draft,
        ]);

        ProductionQueue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'work_center_id' => $workCenter->id,
            'queue_position' => ProductionQueue::query()->where('work_center_id', $workCenter->id)->count() + 1,
            'status' => ProductionQueueStatus::Queued,
        ]);

        return $jobCard;
    }
}
