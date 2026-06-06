<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\AssetDocument;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\Asset360Service;
use App\Services\Assets\AssetCapitalizationService;
use App\Services\Assets\DepreciationCalculationService;
use App\Support\Assets\AssetDepreciationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssetProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_capitalization_self_approval_blocked(): void
    {
        $user = $this->adminUser();
        $candidate = $this->makeCandidate(['quantity' => 3]);

        $this->expectException(ValidationException::class);
        app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 3,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Bulk Asset',
            'branch_id' => $candidate->branch_id,
        ], $user->id, false);
    }

    public function test_capitalization_approve_then_execute(): void
    {
        $capitalizer = $this->adminUser();
        $approver = User::factory()->create(['company_id' => $capitalizer->company_id, 'is_active' => true]);
        $approver->assignRole('Company Admin');

        $candidate = $this->makeCandidate(['quantity' => 2]);
        app(AssetCapitalizationService::class)->approve($candidate, $approver->id);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 2,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Approved Asset',
            'branch_id' => $candidate->branch_id,
        ], $capitalizer->id, false);

        $this->assertCount(2, $assets);
        $this->assertNotNull($candidate->fresh()->approved_at);
    }

    public function test_ad_hoc_depreciation_redirects_to_runs(): void
    {
        $user = $this->adminUser();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->post(route('admin.assets.depreciate', $asset), ['period_date' => now()->toDateString()])
            ->assertRedirect(route('admin.assets.finance.runs.index'));
    }

    public function test_depreciation_respects_start_date(): void
    {
        $asset = $this->makeAsset([
            'depreciation_start_date' => now()->addMonths(2),
            'capitalization_date' => now()->subYear(),
        ]);

        $result = app(DepreciationCalculationService::class)->calculateForPeriod($asset, now()->toDateString());

        $this->assertSame(0.0, $result['depreciation_amount']);
    }

    public function test_asset_intelligence_gates_registered(): void
    {
        $user = $this->adminUser();
        $asset = $this->makeAsset();

        $this->assertTrue(Gate::forUser($user)->allows('asset-analytics.view'));
        $this->assertTrue(Gate::forUser($user)->allows('asset-health.view', $asset));
    }

    public function test_asset_360_workspace_card_exists(): void
    {
        $groups = config('assets_workspaces.groups');
        $labels = collect($groups)->flatMap(fn ($g) => collect($g['items'])->pluck('label'))->all();

        $this->assertContains('Asset 360', $labels);
    }

    public function test_supply_chain_assets_points_to_workspace(): void
    {
        $items = config('supply_chain_workspaces.sections.assets.groups.0.items');
        $this->assertSame('admin.workspaces.assets', $items[0]['route'] ?? null);
    }

    public function test_document_upload_requires_permission(): void
    {
        $asset = $this->makeAsset();
        $viewer = User::factory()->create(['company_id' => $asset->company_id, 'is_active' => true]);
        $viewer->assignRole('Viewer');

        Storage::fake('local');

        $this->actingAs($viewer)
            ->post(route('admin.assets.documents.store', $asset), [
                'document_type' => 'photo',
                'title' => 'Front',
                'file' => UploadedFile::fake()->image('front.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_document_upload_and_download(): void
    {
        $user = $this->adminUser();
        $asset = $this->makeAsset();

        Storage::fake('local');

        $this->actingAs($user)
            ->post(route('admin.assets.documents.store', $asset), [
                'document_type' => 'manual',
                'title' => 'User Manual',
                'file' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = AssetDocument::query()->where('fixed_asset_id', $asset->id)->first();
        $this->assertNotNull($document);

        $this->actingAs($user)
            ->get(route('admin.assets.documents.download', $document))
            ->assertOk();
    }

    public function test_asset_360_utilization_tab_has_no_fake_percentage(): void
    {
        $asset = $this->makeAsset();
        $data = app(Asset360Service::class)->build($asset, Asset360Service::TAB_UTILIZATION);

        $this->assertNull($data['tab_data']['assignment_utilization']);
        $this->assertSame(__('Not yet available'), $data['tab_data']['assignment_utilization_label']);
    }

    protected function adminUser(): User
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
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCandidate(array $overrides = []): \App\Models\Assets\AssetCapitalizationCandidate
    {
        $company = Company::query()->first();
        $branch = Branch::query()->where('company_id', $company->id)->first();
        $category = AssetCategory::query()->create([
            'company_id' => $company->id,
            'name' => 'Machine',
            'code' => 'M-'.uniqid(),
            'asset_type' => AssetType::Machine->value,
            'useful_life_years' => 5,
            'is_active' => true,
        ]);

        return \App\Models\Assets\AssetCapitalizationCandidate::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'candidate_number' => 'CAP-'.uniqid(),
            'quantity' => 3,
            'quantity_capitalized' => 0,
            'unit_cost' => 50000,
            'line_amount' => 150000,
            'asset_category_id' => $category->id,
            'status' => CapitalizationCandidateStatus::Ready,
            'received_date' => now(),
        ], $overrides));
    }
}
