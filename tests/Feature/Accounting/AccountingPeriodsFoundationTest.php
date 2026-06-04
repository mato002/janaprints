<?php

namespace Tests\Feature\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\FiscalYearService;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingPeriodsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
    }

    public function test_generates_twelve_monthly_periods(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        app(SystemSettingsService::class)->set('fiscal_year_start_month', 1, $company->id, null, 'integer');

        $fy = app(FiscalYearService::class)->generate($company->id, 2026, User::factory()->create()->id);

        $this->assertDatabaseHas('fiscal_years', [
            'company_id' => $company->id,
            'code' => 'FY2026',
            'status' => FiscalYearStatus::Open->value,
        ]);

        $this->assertCount(12, $fy->periods);
        $this->assertSame('2026-01', $fy->periods->first()->code);
        $this->assertSame('2026-12', $fy->periods->last()->code);
        $this->assertSame(AccountingPeriodStatus::Open, $fy->periods->first()->status);
    }

    public function test_fiscal_year_respects_july_start_month(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        app(SystemSettingsService::class)->set('fiscal_year_start_month', 7, $company->id, null, 'integer');

        $fy = app(FiscalYearService::class)->generate($company->id, 2025, 1);

        $this->assertSame('2025-07-01', $fy->start_date->toDateString());
        $this->assertSame('2026-06-30', $fy->end_date->toDateString());
        $this->assertSame('2025-07', $fy->periods->first()->code);
        $this->assertSame('2026-06', $fy->periods->last()->code);
    }

    public function test_close_and_lock_period(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);

        $fy = app(FiscalYearService::class)->generate($company->id, 2026, $user->id);
        $period = $fy->periods->first();

        app(\App\Support\Accounting\AccountingPeriodService::class)->close($period, $user->id);
        $this->assertSame(AccountingPeriodStatus::Closed, $period->fresh()->status);

        app(\App\Support\Accounting\AccountingPeriodService::class)->lock($period->fresh(), $user->id);
        $this->assertSame(AccountingPeriodStatus::Locked, $period->fresh()->status);
    }

    public function test_year_end_prep_requires_all_periods_closed(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);
        $fy = app(FiscalYearService::class)->generate($company->id, 2026, $user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(FiscalYearService::class)->beginYearEndPreparation($fy, $user->id);
    }

    public function test_year_end_prep_and_close_fiscal_year(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);

        $fy = app(FiscalYearService::class)->generate($company->id, 2026, $user->id);
        $periodService = app(\App\Support\Accounting\AccountingPeriodService::class);

        foreach ($fy->periods as $period) {
            $periodService->close($period, $user->id);
        }

        app(FiscalYearService::class)->beginYearEndPreparation($fy->fresh(), $user->id);
        $this->assertSame(FiscalYearStatus::YearEndPreparation, $fy->fresh()->status);

        app(\App\Support\Accounting\Close\YearEndCloseService::class)->closeFiscalYear($fy->fresh(), $user->id);
        $this->assertSame(FiscalYearStatus::Closed, $fy->fresh()->status);
    }

    public function test_accountant_can_view_periods_index(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        app(FiscalYearService::class)->generate($company->id, 2026, 1);

        $user = $this->userWithRole('Accountant', $company, $branch);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.accounting.periods.index'))
            ->assertOk()
            ->assertSee(__('Accounting Periods'))
            ->assertSee('FY2026');
    }

    public function test_company_isolation_for_fiscal_year(): void
    {
        $companyA = Company::factory()->create(['code' => 'CA']);
        $companyB = Company::query()->where('code', 'JANA')->firstOrFail();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::query()->where('company_id', $companyB->id)->firstOrFail();

        $fyB = app(FiscalYearService::class)->generate($companyB->id, 2026, 1);
        $userA = $this->userWithRole('Accountant', $companyA, $branchA, ['accounting.periods.view']);

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($userA)
            ->get(route('admin.accounting.periods.fiscal-years.show', $fyB))
            ->assertForbidden();
    }

    public function test_set_current_period(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $fy = app(FiscalYearService::class)->generate($company->id, 2026, User::factory()->create()->id);
        $second = $fy->periods->where('period_number', 2)->first();

        $user = $this->userWithRole('Company Admin', $company, $branch);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.accounting.periods.set-current', $second))
            ->assertRedirect();

        $this->assertTrue($second->fresh()->is_current);
        $this->assertFalse($fy->periods->first()->fresh()->is_current);
    }

    /**
     * @param  list<string>|null  $permissions
     */
    protected function userWithRole(string $role, Company $company, Branch $branch, ?array $permissions = null): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole($role);

        if ($permissions !== null) {
            $user->syncPermissions($permissions);
        }

        return $user;
    }
}
