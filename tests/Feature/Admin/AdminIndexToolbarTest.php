<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminIndexToolbarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_activities_index_renders_compact_toolbar_without_apply_button(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.activities.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.index'))
            ->assertOk()
            ->assertSee('erp-index-toolbar', false)
            ->assertSee(__('Reset'), false)
            ->assertDontSee(__('Apply filters'), false);
    }

    public function test_activities_status_filter_persists_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.activities.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('erp-filter-pill-select--active', false)
            ->assertSee('value="completed"', false);
    }

    public function test_dispatch_delivery_notes_renders_status_pills(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.dispatch.delivery-notes.index'))
            ->assertOk()
            ->assertSee('erp-filter-pill', false)
            ->assertDontSee('>'.__('Filter').'<', false);
    }

    public function test_audit_logs_export_dropdown_renders_with_query_filters(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.operations.audit.index', [
                'search' => 'inventory',
                'date_from' => '2026-01-01',
            ]))
            ->assertOk()
            ->assertSee(__('Export'), false)
            ->assertSee('operations/audit/export', false)
            ->assertSee('search=inventory', false)
            ->assertSee('format=csv', false);
    }

    public function test_assets_export_urls_preserve_active_filters(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.assets.index', ['status' => 'idle', 'search' => 'printer']))
            ->assertOk()
            ->assertSee('erp-index-toolbar', false)
            ->assertSee('erp-bulk-action-bar', false)
            ->assertSee('register/export/csv', false)
            ->assertSee('status=idle', false)
            ->assertSee('search=printer', false);
    }

    public function test_hr_reports_renders_compact_toolbar_without_apply_button(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.reports.hr'))
            ->assertOk()
            ->assertSee('erp-index-toolbar', false)
            ->assertSee(__('Reset'), false)
            ->assertDontSee('>'.__('Apply').'<', false)
            ->assertDontSee('>'.__('Filter').'<', false);
    }

    public function test_accounting_dashboard_renders_compact_toolbar_and_context_line(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.accounting.dashboard'))
            ->assertOk()
            ->assertSee('erp-index-toolbar', false)
            ->assertSee(__('As of'), false)
            ->assertDontSee('>'.__('Filter').'<', false);
    }

    public function test_assets_bulk_bar_hidden_until_selection(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.assets.index'))
            ->assertOk()
            ->assertSee('x-show="selectedCount > 0"', false);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }

        return [$company, $branch, $user];
    }
}
