<?php

namespace Tests\Feature\Accounting;

use App\Enums\GlAccountTypeCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Company;
use App\Support\Accounting\JanaPrintsChartOfAccountsSeedService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountsProductionSeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
    }

    public function test_production_chart_seeds_all_nodes_for_jana(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $report = app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        $this->assertSame('seeded', $report['status']);
        $this->assertTrue($report['hierarchy']['valid']);
        $this->assertSame(46, $report['account_counts']['total_accounts']);
        $this->assertSame(43, $report['account_counts']['postable_accounts']);
        $this->assertSame(9, $report['account_counts']['groups']);
        $this->assertSame(46, $report['account_counts']['active_accounts']);
        $this->assertSame(0, $report['account_counts']['locked_accounts']);
    }

    public function test_seed_is_idempotent_and_prevents_duplicate_codes(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(JanaPrintsChartOfAccountsSeedService::class);

        $service->seedCompany($company);
        $second = $service->seedCompany($company);

        $this->assertSame('skipped', $second['status']);
        $this->assertSame(
            46,
            GlAccount::query()->where('company_id', $company->id)->whereNull('branch_id')->count(),
        );
    }

    public function test_force_resync_updates_without_duplicates(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(JanaPrintsChartOfAccountsSeedService::class);

        $service->seedCompany($company);
        $forced = $service->seedCompany($company, force: true);

        $this->assertSame('seeded', $forced['status']);
        $this->assertTrue($forced['hierarchy']['valid']);
        $this->assertSame(46, GlAccount::query()->where('company_id', $company->id)->count());
    }

    public function test_inventory_parent_child_hierarchy(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        $header = GlAccount::query()->where('company_id', $company->id)->where('code', '1400')->firstOrFail();
        $raw = GlAccount::query()->where('company_id', $company->id)->where('code', '1410')->firstOrFail();

        $this->assertFalse($header->is_postable);
        $this->assertTrue($raw->is_postable);
        $this->assertSame($header->id, $raw->parent_id);
    }

    public function test_printing_revenue_sub_accounts(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        foreach ([
            '4110' => 'Banner Printing',
            '4120' => 'Business Cards',
            '4150' => 'Large Format',
        ] as $code => $name) {
            $this->assertDatabaseHas('gl_accounts', [
                'company_id' => $company->id,
                'code' => $code,
                'name' => $name,
            ]);
        }
    }

    public function test_account_types_cover_six_categories(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $report = app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        foreach (GlAccountTypeCode::cases() as $type) {
            $this->assertGreaterThan(0, $report['account_counts']['by_type'][$type->value] ?? 0);
        }
    }

    public function test_seed_command_succeeds(): void
    {
        $this->artisan('accounting:seed-chart-of-accounts', ['--types' => true])
            ->assertSuccessful();
    }

    public function test_chart_index_shows_dashboard_stats(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = $company->branches()->firstOrFail();
        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Accountant');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.accounting.accounts.index'))
            ->assertOk()
            ->assertSee(__('Total accounts'))
            ->assertSee('46')
            ->assertSee(__('Account types'))
            ->assertSee(__('Account groups'));

        $bankGroup = GlAccountGroup::query()
            ->where('company_id', $company->id)
            ->where('code', '1200')
            ->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.accounts', ['group_id' => $bankGroup->id]))
            ->assertOk()
            ->assertJsonFragment(['code' => '1210', 'name' => 'Equity Bank']);

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.search', ['q' => 'Banner']))
            ->assertOk()
            ->assertJsonFragment(['code' => '4110', 'name' => 'Banner Printing']);
    }

    public function test_multi_company_isolation_on_seed(): void
    {
        $jana = Company::query()->where('code', 'JANA')->firstOrFail();
        $other = Company::factory()->create(['code' => 'OTHR', 'name' => 'Other Co']);

        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($jana);
        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($other);

        $this->assertSame(46, GlAccount::query()->where('company_id', $jana->id)->count());
        $this->assertSame(46, GlAccount::query()->where('company_id', $other->id)->count());
        $this->assertSame(9, GlAccountGroup::query()->where('company_id', $other->id)->count());
    }
}
