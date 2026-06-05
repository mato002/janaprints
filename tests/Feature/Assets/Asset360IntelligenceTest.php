<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetPhysicalCondition;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\Asset360Service;
use App\Services\Assets\AssetAnalyticsService;
use App\Services\Assets\AssetExecutiveIntelligenceService;
use App\Services\Assets\AssetHealthScoreService;
use App\Services\Assets\AssetIntelligenceNotificationService;
use App\Services\Assets\AssetReplacementService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Asset360IntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_asset_360_page_loads(): void
    {
        $user = $this->intelligenceUser();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->get(route('admin.assets.360.show', $asset))
            ->assertOk()
            ->assertSee(__('Asset 360'));
    }

    public function test_health_score_calculation(): void
    {
        $asset = $this->makeAsset(['current_condition' => AssetPhysicalCondition::Good]);
        $result = app(AssetHealthScoreService::class)->score($asset);

        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
        $this->assertNotEmpty($result['factors']);
    }

    public function test_replacement_service_identifies_fully_depreciated_asset(): void
    {
        $asset = $this->makeAsset(['is_fully_depreciated' => true, 'accumulated_depreciation' => 100000]);
        $candidates = app(AssetReplacementService::class)->candidates($asset->company_id);

        $this->assertTrue($candidates->contains(fn ($c) => $c['asset']->id === $asset->id));
    }

    public function test_asset_360_service_builds_all_tabs(): void
    {
        $asset = $this->makeAsset();

        foreach (Asset360Service::TABS as $tab) {
            $data = app(Asset360Service::class)->build($asset, $tab);
            $this->assertSame($tab, $data['active_tab']);
            $this->assertArrayHasKey('tab_data', $data);
        }
    }

    public function test_executive_dashboard_builds(): void
    {
        $this->makeAsset();
        $stats = app(AssetExecutiveIntelligenceService::class)->build((int) Company::query()->first()->id);

        $this->assertArrayHasKey('total_asset_value', $stats);
        $this->assertArrayHasKey('replacement_candidates', $stats);
    }

    public function test_analytics_service_builds(): void
    {
        $this->makeAsset();
        $stats = app(AssetAnalyticsService::class)->build((int) Company::query()->first()->id);

        $this->assertArrayHasKey('by_category', $stats);
        $this->assertArrayHasKey('age_distribution', $stats);
    }

    public function test_intelligence_notifications_scan(): void
    {
        $user = $this->intelligenceUser();
        $this->makeAsset(['acquisition_cost' => 100000]);

        $sent = app(AssetIntelligenceNotificationService::class)->scanCompany($user->company_id, $user->id);
        $this->assertGreaterThanOrEqual(0, $sent);
    }

    public function test_permission_required_for_asset_360(): void
    {
        $asset = $this->makeAsset();
        $user = User::factory()->create([
            'company_id' => $asset->company_id,
            'is_active' => true,
        ]);
        $user->assignRole('Viewer');

        $this->actingAs($user)
            ->get(route('admin.assets.360.show', $asset))
            ->assertForbidden();
    }

    public function test_executive_dashboard_loads(): void
    {
        $user = $this->intelligenceUser();
        $this->makeAsset();

        $this->actingAs($user)
            ->get(route('admin.assets.intelligence.executive'))
            ->assertOk()
            ->assertSee(__('Executive Asset Dashboard'));
    }

    public function test_branch_dashboard_loads(): void
    {
        $user = $this->intelligenceUser();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->get(route('admin.assets.intelligence.branch', ['branch_id' => $asset->branch_id]))
            ->assertOk()
            ->assertSee(__('Branch Asset Intelligence'));
    }

    public function test_tenant_isolation_on_asset_360(): void
    {
        $user = $this->intelligenceUser();
        $other = Company::query()->create(['name' => 'Other', 'code' => 'OTH4', 'is_active' => true]);
        $category = AssetCategory::query()->create([
            'company_id' => $other->id,
            'name' => 'X',
            'code' => 'X',
            'asset_type' => AssetType::Other->value,
            'is_active' => true,
        ]);
        $foreign = FixedAsset::query()->create([
            'company_id' => $other->id,
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-F',
            'asset_name' => 'Foreign',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.360.show', $foreign))
            ->assertForbidden();
    }

    protected function intelligenceUser(): User
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
    protected function makeAsset(array $overrides = []): FixedAsset
    {
        $company = Company::query()->first();
        $category = AssetCategory::query()->create([
            'company_id' => $company->id,
            'name' => 'Equip',
            'code' => 'EQ-'.uniqid(),
            'asset_type' => AssetType::Computer->value,
            'useful_life_years' => 5,
            'is_active' => true,
        ]);

        return FixedAsset::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => Branch::query()->where('company_id', $company->id)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-'.uniqid(),
            'asset_name' => 'Test Asset',
            'acquisition_date' => now()->subYears(2),
            'capitalization_date' => now()->subYears(2),
            'acquisition_cost' => 100000,
            'residual_value' => 0,
            'useful_life_years' => 5,
            'accumulated_depreciation' => 0,
            'net_book_value' => 100000,
            'status' => FixedAssetStatus::Active,
            'current_condition' => AssetPhysicalCondition::Good,
        ], $overrides));
    }
}
