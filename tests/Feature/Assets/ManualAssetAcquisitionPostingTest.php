<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetAcquisitionAccountingStatus;
use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationReconciliationService;
use App\Services\Assets\AssetManualAcquisitionPostingService;
use App\Services\Assets\AssetRegisterService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManualAssetAcquisitionPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

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

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
    }

    public function test_manual_asset_registers_with_not_posted_accounting_status(): void
    {
        $user = $this->assetManager();
        $category = $this->makeCategory();

        $this->actingAs($user)->post(route('admin.assets.store'), [
            'asset_category_id' => $category->id,
            'asset_name' => 'Manual Copier',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 150000,
            'status' => FixedAssetStatus::Active->value,
        ])->assertRedirect();

        $asset = FixedAsset::query()->firstOrFail();

        $this->assertSame(AssetAcquisitionSource::Manual, $asset->acquisition_source);
        $this->assertSame(AssetAcquisitionAccountingStatus::NotPosted, $asset->acquisition_accounting_status);
        $this->assertNull($asset->posted_acquisition_journal_id);
    }

    public function test_option_a_posts_acquisition_journal_on_create(): void
    {
        $user = $this->assetManager(['assets.create', 'assets.acquisition.post']);
        $category = $this->makeCategory();

        $this->actingAs($user)->post(route('admin.assets.store'), [
            'asset_category_id' => $category->id,
            'asset_name' => 'Immediate Post Asset',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 90000,
            'status' => FixedAssetStatus::Active->value,
            'post_acquisition_now' => '1',
        ])->assertRedirect();

        $asset = FixedAsset::query()->firstOrFail();

        $this->assertSame(AssetAcquisitionAccountingStatus::Posted, $asset->acquisition_accounting_status);
        $this->assertNotNull($asset->posted_acquisition_journal_id);

        $journal = Journal::query()->findOrFail($asset->posted_acquisition_journal_id);
        $this->assertSame(PostingEventCode::AssetAcquisitionPosted->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());
    }

    public function test_option_b_post_later_from_asset_profile(): void
    {
        $user = $this->assetManager(['assets.create', 'assets.acquisition.post']);
        $category = $this->makeCategory();

        $asset = app(AssetRegisterService::class)->create([
            'asset_category_id' => $category->id,
            'asset_name' => 'Deferred Post Asset',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 65000,
            'status' => FixedAssetStatus::Active,
        ], $this->company->id, $this->branch->id, $user->id);

        $this->assertSame(AssetAcquisitionAccountingStatus::NotPosted, $asset->acquisition_accounting_status);

        $this->actingAs($user)
            ->post(route('admin.assets.acquisition.post', $asset))
            ->assertRedirect();

        $asset = $asset->fresh();
        $this->assertSame(AssetAcquisitionAccountingStatus::Posted, $asset->acquisition_accounting_status);
        $this->assertNotNull($asset->posted_acquisition_journal_id);
    }

    public function test_view_journal_redirects_to_accounting_journal(): void
    {
        $user = $this->assetManager(['assets.create', 'assets.acquisition.post', 'assets.acquisition.view']);
        $category = $this->makeCategory();
        $asset = $this->makeManualAsset($category, $user);

        app(AssetManualAcquisitionPostingService::class)->post($asset, $user->id);

        $this->assertTrue(Route::has('admin.accounting.journals.show'));

        $this->actingAs($user)
            ->get(route('admin.assets.acquisition.journal', $asset->fresh()))
            ->assertRedirect(route('admin.accounting.journals.show', $asset->fresh()->posted_acquisition_journal_id));
    }

    public function test_reconciliation_flags_manual_assets_not_posted(): void
    {
        $user = $this->assetManager();
        $category = $this->makeCategory();

        $this->makeManualAsset($category, $user, ['acquisition_cost' => 40000]);
        $postedAsset = $this->makeManualAsset($category, $user, ['acquisition_cost' => 25000]);
        app(AssetManualAcquisitionPostingService::class)->post($postedAsset, $user->id);

        $reconciliation = app(AssetCapitalizationReconciliationService::class)->run($this->company->id, $user->id);

        $this->assertSame(1, collect($reconciliation->variance_details)->firstWhere('type', 'manual_not_posted')['count'] ?? 0);
        $this->assertEqualsWithDelta(25000.0, (float) $reconciliation->posted_value, 0.01);
        $this->assertEqualsWithDelta(65000.0, (float) $reconciliation->register_value, 0.01);
    }

    public function test_posting_requires_permission(): void
    {
        $viewer = $this->assetManager(['assets.view']);
        $category = $this->makeCategory();
        $asset = $this->makeManualAsset($category, $viewer);

        $this->actingAs($viewer)
            ->post(route('admin.assets.acquisition.post', $asset))
            ->assertForbidden();
    }

    public function test_retry_posting_recovers_from_failed_status(): void
    {
        $user = $this->assetManager(['assets.create', 'assets.acquisition.post']);
        $category = $this->makeCategory();
        $asset = $this->makeManualAsset($category, $user, ['acquisition_cost' => 0.01]);

        $asset->update([
            'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::Failed,
            'acquisition_posting_error' => 'Simulated failure',
            'acquisition_cost' => 50000,
        ]);

        $this->actingAs($user)
            ->post(route('admin.assets.acquisition.retry', $asset))
            ->assertRedirect();

        $asset = $asset->fresh();
        $this->assertSame(AssetAcquisitionAccountingStatus::Posted, $asset->acquisition_accounting_status);
        $this->assertNull($asset->acquisition_posting_error);
        $this->assertNotNull($asset->posted_acquisition_journal_id);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function assetManager(array $permissions = ['assets.view', 'assets.create']): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function makeCategory(): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => $this->company->id,
            'name' => 'Manual Equipment',
            'code' => 'MAN-'.uniqid(),
            'asset_type' => AssetType::Computer->value,
            'useful_life_years' => 5,
            'useful_life_months' => 60,
            'depreciation_method' => 'straight_line',
            'default_gl_code' => '1530',
            'accumulated_depreciation_gl_code' => '1550',
            'depreciation_expense_gl_code' => '6710',
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeManualAsset(AssetCategory $category, User $user, array $overrides = []): FixedAsset
    {
        return app(AssetRegisterService::class)->create(array_merge([
            'asset_category_id' => $category->id,
            'asset_name' => 'Manual Asset',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 75000,
            'status' => FixedAssetStatus::Active,
        ], $overrides), $this->company->id, $this->branch->id, $user->id);
    }
}
