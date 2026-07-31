<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Production\JobCardJobSheetPresenter;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobCardJobSheetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_offset_job_sheet_renders_printable_view(): void
    {
        [$user, $jobCard] = $this->offsetJobContext();

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.job-sheet', $jobCard))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false)
            ->assertSee(__('Job Sheet'), false)
            ->assertSee(__('Printing specifications'), false)
            ->assertSee(__('Binding specifications'), false);
    }

    public function test_job_sheet_forbidden_without_permission(): void
    {
        [$company, $branch, , $jobCard] = $this->offsetJobContext(withUser: false);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.job-sheet', $jobCard))
            ->assertForbidden();
    }

    public function test_presenter_includes_customer_and_job_number(): void
    {
        [, $jobCard] = $this->offsetJobContext();

        $payload = app(JobCardJobSheetPresenter::class)->present($jobCard);

        $this->assertSame($jobCard->job_card_number, $payload['job_number']);
        $this->assertNotSame('—', $payload['customer_name']);
    }

    /**
     * @return array{0: User, 1: ProductionJobCard}|array{0: Company, 1: Branch, 2: User, 3: ProductionJobCard}
     */
    protected function offsetJobContext(bool $withUser = true): array
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
        $role->syncPermissions(['production.view', 'production.queue.view']);
        $user->assignRole('Production');

        $this->seed(ProductionFoundationSeeder::class);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'production_type' => ProductionType::Offset,
            'created_by' => $user->id,
        ]);

        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch, false));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        if (! $withUser) {
            return [$company, $branch, $user, $jobCard];
        }

        return [$user, $jobCard];
    }
}
