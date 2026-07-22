<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\AssetWriteOffReason;
use App\Enums\AssetWriteOffStatus;
use App\Enums\DepreciationPostingStatus;
use App\Enums\DepreciationRunStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\AssetRegisterReconciliation;
use App\Models\Assets\AssetWriteOff;
use App\Models\Assets\DepreciationRun;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\AssetPeriodControlService;
use App\Services\Assets\AssetReconciliationService;
use App\Services\Assets\AssetWriteOffService;
use App\Services\Assets\DepreciationCalculationService;
use App\Services\Assets\DepreciationRunService;
use App\Support\Assets\AssetDepreciationService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDepreciationAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
    }

    public function test_finance_dashboard_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.assets.finance.dashboard'))
            ->assertForbidden();
    }

    public function test_straight_line_depreciation_calculation(): void
    {
        $asset = $this->makeAsset(['acquisition_cost' => 120000, 'residual_value' => 0, 'useful_life_years' => 10]);
        $calc = app(DepreciationCalculationService::class)->calculateForPeriod($asset, now()->toDateString());

        $this->assertSame(1000.0, $calc['monthly_depreciation']);
        $this->assertSame(12000.0, $calc['annual_depreciation']);
        $this->assertSame(1000.0, $calc['depreciation_amount']);
    }

    public function test_depreciation_run_creation_preview_and_execution_without_posting(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 60000, 'useful_life_years' => 5]);
        $period = now()->format('Y-m');

        $run = app(DepreciationRunService::class)->createDraft(
            $asset->company_id,
            $period,
            $user->id,
            null,
            false,
        );

        $this->assertStringStartsWith('DR-', $run->run_number);
        $preview = app(DepreciationRunService::class)->preview($run);
        $this->assertNotEmpty($preview['preview']);

        $completed = app(DepreciationRunService::class)->execute($run, $user->id, false);
        $this->assertSame(DepreciationRunStatus::Completed, $completed->status);
        $this->assertDatabaseHas('asset_depreciation_entries', [
            'fixed_asset_id' => $asset->id,
            'posting_status' => DepreciationPostingStatus::Draft->value,
        ]);
    }

    public function test_duplicate_depreciation_run_for_period_is_blocked(): void
    {
        $user = $this->financeUser();
        $period = now()->format('Y-m');
        $companyId = Company::query()->first()->id;

        DepreciationRun::query()->create([
            'company_id' => $companyId,
            'run_number' => 'DR-TEST-001',
            'period' => $period,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'run_date' => now(),
            'status' => DepreciationRunStatus::Completed,
            'executed_by' => $user->id,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(DepreciationRunService::class)->createDraft($companyId, $period, $user->id);
    }

    public function test_journal_posting_is_idempotent(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 12000, 'useful_life_years' => 1]);

        $entry = AssetDepreciationService::runPeriod($asset, now()->startOfMonth()->toDateString(), $user->id);
        $journalId = $entry->fresh()->posted_journal_id;
        $this->assertNotNull($journalId);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        AssetDepreciationService::runPeriod($asset, now()->startOfMonth()->toDateString(), $user->id);
    }

    public function test_closed_period_blocks_depreciation_posting(): void
    {
        $user = $this->financeUser();
        $companyId = Company::query()->first()->id;
        $period = AccountingPeriod::query()->where('company_id', $companyId)->where('is_current', true)->first();
        app(\App\Support\Accounting\AccountingPeriodService::class)->close($period, $user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AssetPeriodControlService::class)->assertPeriodOpenForPosting(
            $companyId,
            $period->end_date->toDateString(),
        );
    }

    public function test_reconciliation_detects_register_totals(): void
    {
        $user = $this->financeUser();
        $this->makeAsset(['acquisition_cost' => 50000]);
        $this->makeAsset(['acquisition_cost' => 30000]);

        $record = app(AssetReconciliationService::class)->run($user->company_id, $user->id);

        $this->assertSame(80000.0, (float) $record->register_cost);
        $this->assertInstanceOf(AssetRegisterReconciliation::class, $record);
    }

    public function test_write_off_workflow_without_posting_when_not_approved(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset();

        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Damaged->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);

        $this->assertStringStartsWith('AWO-', $writeOff->writeoff_no);
        $this->assertSame(AssetWriteOffStatus::Approved, $writeOff->status);
    }

    public function test_financial_profile_page_loads(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->get(route('admin.assets.finance.profile', $asset))
            ->assertOk()
            ->assertSee(__('Financial Summary'));
    }

    public function test_tenant_isolation_on_finance_routes(): void
    {
        $user = $this->financeUser();
        $otherCompany = Company::query()->create(['name' => 'Other', 'code' => 'OTH2', 'is_active' => true]);
        $category = AssetCategory::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Other',
            'code' => 'O',
            'asset_type' => AssetType::Computer->value,
            'is_active' => true,
        ]);
        $otherAsset = FixedAsset::query()->create([
            'company_id' => $otherCompany->id,
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-X',
            'asset_name' => 'Foreign',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.finance.profile', $otherAsset))
            ->assertForbidden();
    }

    protected function financeUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function makeCategory(): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => Company::query()->first()->id,
            'name' => 'Equipment',
            'code' => 'EQP-'.uniqid(),
            'asset_type' => AssetType::Computer->value,
            'useful_life_years' => 5,
            'useful_life_months' => 60,
            'depreciation_method' => 'straight_line',
            'default_gl_code' => '1530',
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeAsset(array $overrides = []): FixedAsset
    {
        $category = $this->makeCategory();

        return FixedAsset::query()->create(array_merge([
            'company_id' => $category->company_id,
            'branch_id' => Branch::query()->where('company_id', $category->company_id)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-'.uniqid(),
            'asset_name' => 'Finance Test Asset',
            'acquisition_date' => now()->subYear(),
            'capitalization_date' => now()->subYear(),
            'acquisition_cost' => 100000,
            'residual_value' => 0,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'depreciation_start_date' => now()->subYear(),
            'accumulated_depreciation' => 0,
            'net_book_value' => 100000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }
}
