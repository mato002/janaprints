<?php

namespace Tests\Feature\Accounting;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\AccountingWorkspacePresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccountingWorkspaceConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_accounting_hub_renders_dashboard_workspace_desk(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.accounting'));

        $response->assertOk();
        $response->assertSee(route('admin.accounting.dashboard', ['embedded' => '1']), false);
        $response->assertSee(__('General Ledger'), false);
    }

    public function test_general_ledger_section_lists_gl_features(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.accounting.section', ['section' => 'general-ledger']));

        $response->assertOk();
        $response->assertSee('module-workspace-switcher--secondary', false);
        $response->assertSee('Journals', false);
        $response->assertSee('Trial Balance', false);
        $response->assertSee('embedded=1', false);
        $response->assertSee('accounting/journals', false);
    }

    public function test_existing_accounting_routes_remain_registered(): void
    {
        $this->assertTrue(Route::has('admin.accounting.journals.index'));
        $this->assertTrue(Route::has('admin.invoices.index'));
        $this->assertTrue(Route::has('admin.payables.bills.index'));
        $this->assertTrue(Route::has('admin.tax.codes.index'));
        $this->assertTrue(Route::has('admin.accounting.accounts.index'));
    }

    public function test_accounting_active_routes_include_child_modules(): void
    {
        $patterns = app(AccountingWorkspacePresenter::class)->collectActiveRoutes();

        $this->assertContains('admin.workspaces.accounting', $patterns);
        $this->assertContains('admin.accounting.journals.*', $patterns);
        $this->assertContains('admin.invoices.*', $patterns);
        $this->assertContains('admin.tax.*', $patterns);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
