<?php

namespace Tests\Feature\Reports;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Financial360Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_financial_360_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.financial360'))
            ->assertOk()
            ->assertSee(__('Financial 360'), false)
            ->assertSee(__('Module not ready'), false);
    }

    public function test_aging_buckets_safe_on_empty_db(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.financial360', ['from_date' => '2026-01-01', 'to_date' => '2026-06-05']))
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
