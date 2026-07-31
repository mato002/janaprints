<?php

namespace Tests\Feature\Reports;

use App\Enums\CustomerStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Commercial360Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function getCommercial360(User $user, array $query = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.reports.commercial360', array_merge(['embedded' => '1'], $query)));
    }

    public function test_commercial_360_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->getCommercial360($user)
            ->assertOk()
            ->assertSee(__('Commercial 360'), false)
            ->assertSee(__('Commercial Summary'), false);
    }

    public function test_commercial_360_with_active_customers(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $this->getCommercial360($user)
            ->assertOk()
            ->assertSee(__('Total customers'), false);
    }

    public function test_branch_filter(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->getCommercial360($user, ['branch_id' => $branch->id])
            ->assertOk();
    }

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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
