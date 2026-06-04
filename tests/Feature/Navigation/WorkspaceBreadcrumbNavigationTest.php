<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Navigation\WorkspaceNavigationResolver;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceBreadcrumbNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $this->user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $this->user->assignRole('Company Admin');
    }

    public function test_trial_balance_resolves_financial_reports_parent(): void
    {
        $resolver = app(WorkspaceNavigationResolver::class);
        $context = $resolver->resolve('admin.accounting.reports.trial-balance', 'Trial Balance');

        $this->assertNotNull($context);
        $this->assertSame('Financial Reports', $context['parent_workspace_title']);
        $this->assertStringContainsString('general-ledger', $context['parent_url'] ?? '');
        $this->assertTrue($context['show_back']);

        $labels = array_column($context['breadcrumbs'], 'label');
        $this->assertContains('Accounting', $labels);
        $this->assertContains('Financial Reports', $labels);
        $this->assertSame('Trial Balance', end($labels));
    }

    public function test_customer_statement_resolves_receivables_parent(): void
    {
        $context = app(WorkspaceNavigationResolver::class)
            ->resolve('admin.receivables.statement', 'Customer Statement');

        $this->assertNotNull($context);
        $this->assertSame('Receivables', $context['parent_workspace_title']);
        $this->assertStringContainsString('receivables', $context['parent_url'] ?? '');
    }

    public function test_trial_balance_page_renders_back_link_and_clickable_breadcrumbs(): void
    {
        $response = $this->actingAs($this->user)->get(
            route('admin.accounting.reports.trial-balance').'?from_date=2026-01-01&to_date=2026-06-30'
        );

        $response->assertOk();
        $response->assertSee('Back to Financial Reports', false);
        $response->assertSee('Financial Reports', false);
        $response->assertSee(route('admin.workspaces.accounting'), false);
        $response->assertSee('workspaces/accounting/general-ledger', false);
        $response->assertSee('from_date=2026-01-01', false);
    }

    public function test_chart_of_accounts_back_to_accounting_setup(): void
    {
        $context = app(WorkspaceNavigationResolver::class)
            ->resolve('admin.accounting.accounts.index', 'Chart of Accounts');

        $this->assertNotNull($context);
        $this->assertSame('Accounting Setup', $context['parent_workspace_title']);
        $this->assertStringContainsString('setup', $context['parent_url'] ?? '');
    }

    public function test_preserves_query_in_parent_url_from_session(): void
    {
        $this->actingAs($this->user)->get(
            route('admin.workspaces.accounting.section', ['section' => 'general-ledger']).'?period_id=9'
        );

        $context = app(WorkspaceNavigationResolver::class)
            ->resolve('admin.accounting.reports.trial-balance', 'Trial Balance');

        $this->assertStringContainsString('period_id=9', $context['parent_url'] ?? '');
    }
}
