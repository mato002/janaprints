<?php

namespace Tests\Unit\Support\AccessControl;

use App\Support\AccessControl\RoleGovernancePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleGovernancePresenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_governance_panel_counts_lifecycle_statuses(): void
    {
        $presenter = app(RoleGovernancePresenter::class);

        $panel = $presenter->governancePanel();

        $this->assertArrayHasKey('total_roles', $panel);
        $this->assertArrayHasKey('active', $panel);
        $this->assertArrayHasKey('draft', $panel);
        $this->assertArrayHasKey('broken', $panel);
        $this->assertArrayHasKey('unused', $panel);
        $this->assertArrayHasKey('assigned_users', $panel);
        $this->assertGreaterThan(0, $panel['total_roles']);
    }

    public function test_health_status_logic(): void
    {
        $presenter = app(RoleGovernancePresenter::class);

        $companyAdmin = Role::findByName('Company Admin', 'web');
        $companyAdmin->loadCount(['users', 'permissions']);
        $this->assertSame('draft', $presenter->healthFor($companyAdmin)['status']);

        $empty = Role::create(['name' => 'Empty Role', 'guard_name' => 'web']);
        $empty->loadCount(['users', 'permissions']);
        $this->assertSame('unused', $presenter->healthFor($empty)['status']);

        $draft = Role::create(['name' => 'Draft Role', 'guard_name' => 'web']);
        $draft->syncPermissions(['crm.customers.view']);
        $draft->loadCount(['users', 'permissions']);
        $this->assertSame('draft', $presenter->healthFor($draft)['status']);
    }

    public function test_category_for_accountant_is_finance(): void
    {
        $presenter = app(RoleGovernancePresenter::class);

        $category = $presenter->categoryFor('Accountant');

        $this->assertSame('finance', $category['key']);
        $this->assertSame(__('Finance'), $category['label']);
    }
}
