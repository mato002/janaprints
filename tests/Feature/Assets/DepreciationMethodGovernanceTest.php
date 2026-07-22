<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\DepreciationMethod;
use App\Enums\DepreciationRunStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\AssetPeriodControlService;
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

class DepreciationMethodGovernanceTest extends TestCase
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

    public function test_selectable_methods_only_include_implemented_cases(): void
    {
        $selectable = DepreciationMethod::selectableCases();

        $this->assertCount(1, $selectable);
        $this->assertSame(DepreciationMethod::StraightLine, $selectable[0]);
        $this->assertFalse(DepreciationMethod::ReducingBalance->isSelectable());
        $this->assertFalse(DepreciationMethod::UnitsOfProduction->isSelectable());
    }

    public function test_category_create_rejects_unimplemented_depreciation_method(): void
    {
        $user = $this->assetsAdmin();

        $this->actingAs($user)
            ->post(route('admin.assets.categories.store'), [
                'name' => 'Heavy Equipment',
                'asset_type' => AssetType::Machine->value,
                'useful_life_years' => 10,
                'depreciation_method' => DepreciationMethod::ReducingBalance->value,
            ])
            ->assertSessionHasErrors('depreciation_method');
    }

    public function test_category_create_accepts_straight_line_method(): void
    {
        $user = $this->assetsAdmin();

        $this->actingAs($user)
            ->post(route('admin.assets.categories.store'), [
                'name' => 'Office Equipment',
                'asset_type' => AssetType::Computer->value,
                'useful_life_years' => 5,
                'depreciation_method' => DepreciationMethod::StraightLine->value,
            ])
            ->assertRedirect(route('admin.assets.index').'#asset-categories');

        $this->assertDatabaseHas('asset_categories', [
            'name' => 'Office Equipment',
            'depreciation_method' => DepreciationMethod::StraightLine->value,
        ]);
    }

    public function test_category_form_hides_unimplemented_depreciation_methods(): void
    {
        $user = $this->assetsAdmin();

        $this->actingAs($user)
            ->get(route('admin.assets.categories.create'))
            ->assertOk()
            ->assertSee(DepreciationMethod::StraightLine->label(), false)
            ->assertDontSee(DepreciationMethod::ReducingBalance->label(), false)
            ->assertDontSee(DepreciationMethod::UnitsOfProduction->label(), false);
    }

    public function test_depreciation_run_skips_assets_with_unimplemented_method(): void
    {
        $user = $this->assetsAdmin();
        $supported = $this->makeAsset(['depreciation_method' => DepreciationMethod::StraightLine->value]);
        $unsupported = $this->makeAsset(['depreciation_method' => DepreciationMethod::ReducingBalance->value]);
        $period = now()->format('Y-m');

        $run = app(DepreciationRunService::class)->createDraft(
            $supported->company_id,
            $period,
            $user->id,
        );

        $preview = app(DepreciationRunService::class)->preview($run);
        $previewAssetIds = collect($preview['preview'])->pluck('asset_id')->all();

        $this->assertContains($supported->id, $previewAssetIds);
        $this->assertNotContains($unsupported->id, $previewAssetIds);

        $completed = app(DepreciationRunService::class)->execute($run, $user->id, false);

        $this->assertSame(DepreciationRunStatus::Completed, $completed->status);
        $this->assertDatabaseHas('asset_depreciation_entries', [
            'fixed_asset_id' => $supported->id,
        ]);
        $this->assertDatabaseMissing('asset_depreciation_entries', [
            'fixed_asset_id' => $unsupported->id,
        ]);
    }

    public function test_single_asset_depreciation_still_blocks_unimplemented_method(): void
    {
        $user = $this->assetsAdmin();
        $asset = $this->makeAsset(['depreciation_method' => DepreciationMethod::UnitsOfProduction->value]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        AssetDepreciationService::runPeriod($asset, now()->startOfMonth()->toDateString(), $user->id);
    }

    public function test_closed_period_still_blocks_depreciation_posting(): void
    {
        $user = $this->assetsAdmin();
        $companyId = Company::query()->first()->id;
        $period = AccountingPeriod::query()->where('company_id', $companyId)->where('is_current', true)->first();
        app(\App\Support\Accounting\AccountingPeriodService::class)->close($period, $user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AssetPeriodControlService::class)->assertPeriodOpenForPosting(
            $companyId,
            $period->end_date->toDateString(),
        );
    }

    public function test_financial_profile_reports_unsupported_method_without_crashing(): void
    {
        $user = $this->assetsAdmin();
        $asset = $this->makeAsset(['depreciation_method' => DepreciationMethod::ReducingBalance->value]);

        $profile = app(DepreciationCalculationService::class)->financialProfile($asset);

        $this->assertSame(DepreciationMethod::ReducingBalance, $profile['depreciation_method']);
        $this->assertFalse($profile['depreciation_supported']);
        $this->assertSame(0.0, $profile['monthly_depreciation']);

        $this->actingAs($user)
            ->get(route('admin.assets.finance.profile', $asset))
            ->assertOk()
            ->assertSee(DepreciationMethod::ReducingBalance->label(), false)
            ->assertSee(__('Not supported for automated runs'), false);
    }

    public function test_asset_register_coerces_unsupported_method_to_straight_line(): void
    {
        $user = $this->assetsAdmin();
        $category = $this->makeCategory();

        $asset = app(\App\Services\Assets\AssetRegisterService::class)->create([
            'asset_category_id' => $category->id,
            'asset_name' => 'Coerced Asset',
            'acquisition_date' => now()->subYear(),
            'capitalization_date' => now()->subYear(),
            'acquisition_cost' => 50000,
            'depreciation_method' => DepreciationMethod::ReducingBalance->value,
        ], $category->company_id, Branch::query()->where('company_id', $category->company_id)->value('id'), $user->id);

        $this->assertSame(DepreciationMethod::StraightLine, $asset->depreciation_method);
    }

    protected function assetsAdmin(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCategory(array $overrides = []): AssetCategory
    {
        return AssetCategory::query()->create(array_merge([
            'company_id' => Company::query()->first()->id,
            'name' => 'Equipment',
            'code' => 'EQP-'.uniqid(),
            'asset_type' => AssetType::Computer->value,
            'useful_life_years' => 5,
            'useful_life_months' => 60,
            'depreciation_method' => DepreciationMethod::StraightLine->value,
            'default_gl_code' => '1530',
            'is_active' => true,
        ], $overrides));
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
            'asset_name' => 'Governance Test Asset',
            'acquisition_date' => now()->subYear(),
            'capitalization_date' => now()->subYear(),
            'acquisition_cost' => 60000,
            'residual_value' => 0,
            'useful_life_years' => 5,
            'depreciation_method' => DepreciationMethod::StraightLine->value,
            'depreciation_start_date' => now()->subYear(),
            'accumulated_depreciation' => 0,
            'net_book_value' => 60000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }
}
