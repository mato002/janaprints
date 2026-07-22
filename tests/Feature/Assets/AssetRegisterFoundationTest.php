<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetRegisterFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_assets_workspace_hub_loads(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->followingRedirects()
            ->get(route('admin.workspaces.assets'))
            ->assertOk()
            ->assertSee(__('Asset Management'), false)
            ->assertSee(__('Machines'), false)
            ->assertDontSee(__('Asset Dashboard'), false);
    }

    public function test_supply_chain_assets_section_redirects_to_canonical_workspace(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.supply-chain.section', ['section' => 'assets']))
            ->assertRedirect(route('admin.workspaces.assets'));
    }

    public function test_asset_register_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->getAssetManagement()
            ->assertForbidden();
    }

    public function test_asset_create_renders_modal_panel(): void
    {
        $user = $this->companyAdmin();
        $this->makeCategory();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.assets.create'))
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Register asset'), false);
    }

    public function test_asset_management_workspace_combines_register_categories_and_kpis(): void
    {
        $user = $this->companyAdmin();
        $category = $this->makeCategory(['name' => 'Production Machines']);
        $this->makeAsset($category, ['asset_name' => 'Heidelberg SM74']);

        $this->actingAs($user)
            ->getAssetManagement()
            ->assertOk()
            ->assertSee(__('Asset Management'), false)
            ->assertSee(__('Total Assets'), false)
            ->assertSee(__('Asset Categories'), false)
            ->assertSee(__('Asset Register'), false)
            ->assertSee('Production Machines', false)
            ->assertSee('Heidelberg SM74', false);
    }

    public function test_asset_register_index_links_open_create_in_modal(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->followingRedirects()
            ->get(route('admin.workspaces.assets'))
            ->assertOk()
            ->assertSee('data-erp-modal-open', false)
            ->assertSee(route('admin.assets.create'), false);
    }

    public function test_asset_creation_and_numbering(): void
    {
        $user = $this->companyAdmin();
        $category = $this->makeCategory();

        $response = $this->actingAs($user)->post(route('admin.assets.store'), [
            'asset_category_id' => $category->id,
            'asset_name' => 'HP Indigo',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 2500000,
            'status' => FixedAssetStatus::Active->value,
        ]);

        $asset = FixedAsset::query()->first();
        $this->assertNotNull($asset);
        $response->assertRedirect(route('admin.assets.show', $asset));
        $this->assertStringStartsWith('AST-', $asset->asset_number);
        $this->assertStringContainsString((string) now()->year, $asset->asset_number);
    }

    public function test_asset_update(): void
    {
        $user = $this->companyAdmin();
        $asset = $this->makeAsset($this->makeCategory());

        $this->actingAs($user)
            ->put(route('admin.assets.update', $asset), [
                'asset_category_id' => $asset->asset_category_id,
                'asset_name' => 'Updated Press',
                'acquisition_date' => $asset->acquisition_date->toDateString(),
                'acquisition_cost' => 3000000,
                'status' => FixedAssetStatus::Idle->value,
            ])
            ->assertRedirect(route('admin.assets.show', $asset));

        $this->assertSame('Updated Press', $asset->fresh()->asset_name);
        $this->assertSame(FixedAssetStatus::Idle, $asset->fresh()->status);
    }

    public function test_asset_assignment_creates_history(): void
    {
        $user = $this->companyAdmin();
        $assignee = User::factory()->create([
            'company_id' => $user->company_id,
            'is_active' => true,
        ]);
        $asset = $this->makeAsset($this->makeCategory());

        $this->actingAs($user)
            ->post(route('admin.assets.assign', $asset), [
                'assignment_type' => 'user',
                'assigned_to_user_id' => $assignee->id,
            ])
            ->assertRedirect();

        $this->assertSame($assignee->id, $asset->fresh()->assigned_to_user_id);
        $this->assertDatabaseHas('asset_assignment_histories', [
            'fixed_asset_id' => $asset->id,
            'assigned_to_user_id' => $assignee->id,
        ]);
    }

    public function test_asset_filtering_by_status(): void
    {
        $user = $this->companyAdmin();
        $category = $this->makeCategory();
        $this->makeAsset($category, ['status' => FixedAssetStatus::Active, 'asset_name' => 'Active Only Asset']);
        $this->makeAsset($category, ['status' => FixedAssetStatus::Idle, 'asset_name' => 'Idle Asset']);

        $this->actingAs($user)
            ->getAssetManagement(['status' => FixedAssetStatus::Idle->value])
            ->assertOk()
            ->assertSee('Idle Asset', false)
            ->assertSee('Showing 1–1 of 1', false);
    }

    public function test_category_crud_and_archive(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->post(route('admin.assets.categories.store'), [
                'name' => 'Test Machines',
                'code' => 'TM',
                'asset_type' => AssetType::Machine->value,
                'useful_life_years' => 6,
                'default_gl_code' => '1530',
            ])
            ->assertRedirect(route('admin.assets.index').'#asset-categories');

        $category = AssetCategory::query()->where('code', 'TM')->first();
        $this->assertNotNull($category);

        $this->actingAs($user)
            ->put(route('admin.assets.categories.update', $category), [
                'name' => 'Test Machines Updated',
                'code' => 'TM',
                'asset_type' => AssetType::Machine->value,
                'useful_life_years' => 7,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.assets.index').'#asset-categories');

        $this->assertSame('Test Machines Updated', $category->fresh()->name);

        $this->actingAs($user)
            ->post(route('admin.assets.categories.archive', $category))
            ->assertRedirect(route('admin.assets.index').'#asset-categories');

        $this->assertNotNull($category->fresh()->archived_at);
    }

    public function test_tenant_isolation_on_asset_show(): void
    {
        $user = $this->companyAdmin();
        $foreignCompany = Company::query()->create([
            'name' => 'Foreign Co',
            'code' => 'FOR',
            'is_active' => true,
        ]);
        $foreignCategory = AssetCategory::query()->create([
            'company_id' => $foreignCompany->id,
            'name' => 'Foreign',
            'code' => 'F',
            'asset_type' => AssetType::Other->value,
            'useful_life_months' => 60,
            'useful_life_years' => 5,
            'is_active' => true,
        ]);
        $foreignAsset = FixedAsset::query()->create([
            'company_id' => $foreignCompany->id,
            'asset_category_id' => $foreignCategory->id,
            'asset_number' => 'AST-FOREIGN-001',
            'asset_name' => 'Foreign Asset',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.show', $foreignAsset))
            ->assertNotFound();
    }

    public function test_branch_context_limits_register_listing(): void
    {
        $company = Company::query()->first();
        $branchA = Branch::query()->where('company_id', $company->id)->first();
        $branchB = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Branch B',
            'code' => 'BRB',
            'is_active' => true,
        ]);
        $user = $this->companyAdmin();
        $category = $this->makeCategory();

        $this->makeAsset($category, ['branch_id' => $branchA->id, 'asset_name' => 'Branch A Asset']);
        $this->makeAsset($category, ['branch_id' => $branchB->id, 'asset_name' => 'Branch B Asset']);

        session(['active_branch_id' => $branchB->id]);

        $this->actingAs($user)
            ->getAssetManagement()
            ->assertOk()
            ->assertSee('Branch B Asset', false)
            ->assertDontSee('Branch A Asset', false);
    }

    protected function getAssetManagement(array $query = [])
    {
        return $this->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.assets.index', array_merge(['embedded' => '1'], $query)));
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->first();
        $user = User::factory()->create([
            'company_id' => $company->id,
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
            'name' => 'Printers',
            'code' => 'PRN',
            'asset_type' => AssetType::Printer->value,
            'useful_life_months' => 60,
            'useful_life_years' => 5,
            'default_gl_code' => '1520',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeAsset(AssetCategory $category, array $overrides = []): FixedAsset
    {
        return FixedAsset::query()->create(array_merge([
            'company_id' => $category->company_id,
            'branch_id' => Branch::query()->where('company_id', $category->company_id)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-'.fake()->unique()->numerify('####'),
            'asset_name' => 'HP Indigo',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }
}
