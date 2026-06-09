<?php

namespace Tests\Feature\Reports;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsIntelligenceActivationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private array $reportRoutes = [
        'executive' => 'admin.reports.executive',
        'commercial' => 'admin.reports.commercial',
        'production' => 'admin.reports.production',
        'inventory' => 'admin.reports.inventory',
        'procurement' => 'admin.reports.procurement',
        'accounting' => 'admin.reports.accounting',
        'hr' => 'admin.reports.hr',
        'kpi' => 'admin.reports.kpi',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_intelligence_report_routes_are_registered(): void
    {
        foreach ($this->reportRoutes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route: {$routeName}");
        }
    }

    public function test_reports_workspace_desk_embeds_active_report_content(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.reports.section', ['section' => 'executive']));

        $response->assertOk();
        $response->assertSee('module-workspace-switcher--primary', false);
        $response->assertSee(route('admin.reports.executive', ['embedded' => '1']), false);
    }

    #[DataProvider('reportPageProvider')]
    public function test_report_pages_render_read_only_placeholders(string $routeName, string $heading): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'kpi.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route($routeName, [
            'from_date' => '2026-01-01',
            'to_date' => '2026-06-05',
            'branch_id' => $branch->id,
        ]));

        if ($routeName === 'admin.reports.inventory') {
            $response->assertRedirect(route('admin.inventory.reports.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-06-05',
                'branch_id' => $branch->id,
            ]));

            return;
        }

        $response->assertOk();
        $response->assertSee($heading, false);
        $response->assertSee('Reports &amp; Intelligence', false);

        if ($routeName === 'admin.reports.commercial') {
            $response->assertSee(__('Commercial 360'), false);
            $response->assertDontSee(__('Placeholder — module not connected yet'), false);

            return;
        }

        $response->assertSee(__('All branches'), false);
        $response->assertSee(__('Export'), false);
        $response->assertSee('erp-index-toolbar', false);
        $response->assertDontSee(__('Apply filters'), false);

        if ($routeName === 'admin.reports.production') {
            $response->assertSee(__('Reporting Catalog'), false);
            $response->assertDontSee(__('No report data yet'), false);

            return;
        }

        if ($routeName !== 'admin.reports.executive' && $routeName !== 'admin.reports.kpi') {
            $response->assertSee(__('No report data yet'), false);
        }
    }

    public function test_report_pages_require_reports_view_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.executive'))
            ->assertForbidden();
    }

    public function test_kpi_page_allows_kpi_view_without_reports_view(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['kpi.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi'))
            ->assertOk()
            ->assertSee(__('KPI Center'), false);
    }

    public function test_kpi_page_allows_reports_view_without_kpi_view(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.kpi'))
            ->assertOk();
    }

    public function test_executive_dashboard_shows_expected_widgets(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.reports.executive'));

        $response->assertOk();
        foreach (['Open quotations', 'Active jobs', 'Receivables'] as $label) {
            $response->assertSee(__($label), false);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function reportPageProvider(): array
    {
        return [
            'executive' => ['admin.reports.executive', 'Executive Dashboard'],
            'commercial' => ['admin.reports.commercial', 'Commercial Reports'],
            'production' => ['admin.reports.production', 'Production Reports'],
            'inventory' => ['admin.reports.inventory', 'Inventory Reports'],
            'procurement' => ['admin.reports.procurement', 'Procurement Reports'],
            'accounting' => ['admin.reports.accounting', 'Accounting Reports'],
            'hr' => ['admin.reports.hr', 'HR Reports'],
            'kpi' => ['admin.reports.kpi', 'KPI Center'],
        ];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
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

        $role = Role::findByName('Viewer', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
