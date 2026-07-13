<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecutiveAnalyticsPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_revenue_tab_requires_executive_analytics_permission(): void
    {
        [$company, $branch, $viewer] = $this->userWith(['printing.executive.view']);

        $this->actingAs($viewer)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.executive-intelligence', ['tab' => 'revenue', 'embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Revenue Forecast'))
            ->assertSee(__('Executive analytics permission required to view revenue forecasts.'));

        [, , $analyst] = $this->userWith(['printing.executive.view', 'printing.executive.analytics']);

        $this->actingAs($analyst)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.executive-intelligence', ['tab' => 'revenue', 'embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Revenue Forecast'))
            ->assertSee(__('Period'), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWith(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
