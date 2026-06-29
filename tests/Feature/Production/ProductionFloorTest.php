<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionFloorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_production_floor_page_loads_for_authorized_user(): void
    {
        [$company, $branch, $user] = $this->productionContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk()
            ->assertSee(__('Production Floor'), false)
            ->assertSee(__('At vendor'), false);
    }

    public function test_floor_panel_returns_job_context_json(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->getJson(route('admin.production.floor.panel', $jobCard))
            ->assertOk()
            ->assertJsonPath('header.job_number', $jobCard->job_card_number)
            ->assertJsonStructure(['primary_action', 'outsource', 'fulfilment', 'links']);
    }

    public function test_floor_filters_at_vendor_stage(): void
    {
        [$company, $branch, $customer, $user, $jobCard] = $this->productionContext(withJob: true);

        $jobCard->update(['status' => ProductionJobCardStatus::Outsourced]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $this->actingAs($user)
            ->get(route('admin.production.floor', ['stage' => 'at_vendor', 'embedded' => 1]))
            ->assertOk()
            ->assertSee($jobCard->job_card_number, false);
    }

    public function test_floor_lists_newest_job_cards_first(): void
    {
        [$company, $branch, $user] = $this->productionContext(withJob: false);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $older = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
            'created_at' => now()->subDay(),
            'required_date' => now()->addDays(3),
        ]);

        $newer = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
            'created_at' => now(),
            'required_date' => now()->addDays(10),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($company, $branch));

        $response = $this->actingAs($user)
            ->get(route('admin.production.floor', ['embedded' => 1]))
            ->assertOk();

        $this->assertLessThan(
            strpos($response->getContent(), $older->job_card_number),
            strpos($response->getContent(), $newer->job_card_number),
        );
    }

    /**
     * @return array{0: Company, 1: Branch, 2?: Customer, 3: User, 4?: ProductionJobCard}
     */
    protected function productionContext(bool $withJob = false): array
    {
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
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['production.view', 'production.create', 'production.edit', 'production.start', 'production.complete', 'machines.assign']);
        $user->assignRole('Production');

        if (! $withJob) {
            return [$company, $branch, $user];
        }

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $customer, $user, $jobCard];
    }
}
