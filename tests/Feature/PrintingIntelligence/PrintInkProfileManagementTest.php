<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\User;
use App\Services\PrintingIntelligence\InkCostProfileService;
use App\Services\PrintingIntelligence\PrintInkProfileManagementService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintInkProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_company_admin_can_create_ink_profile(): void
    {
        [$company, $branch, $user] = $this->userWithRole('Company Admin');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.ink-profiles.store'), [
                'name' => 'CMYK Standard',
                'ink_type' => PrintInkType::Cmyk->value,
                'cartridge_cost' => 5000,
                'estimated_ml' => 1000,
                'active' => 1,
            ])
            ->assertRedirect(route('admin.printing-intelligence.ink-profiles.index'));

        $this->assertDatabaseHas('print_ink_profiles', [
            'company_id' => $company->id,
            'name' => 'CMYK Standard',
            'active' => true,
        ]);
    }

    public function test_production_can_create_ink_profile(): void
    {
        [$company, $branch, $user] = $this->userWithRole('Production');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.ink-profiles.store'), [
                'name' => 'EcoSolvent Roll',
                'ink_type' => PrintInkType::EcoSolvent->value,
                'cartridge_cost' => 8000,
                'estimated_ml' => 2000,
                'active' => 1,
            ])
            ->assertRedirect();

        $this->assertSame(1, PrintInkProfile::query()->where('company_id', $company->id)->count());
    }

    public function test_unauthorized_user_cannot_manage(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.ink-profiles.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.ink-profiles.store'), [
                'name' => 'Blocked',
                'ink_type' => PrintInkType::Cmyk->value,
                'cartridge_cost' => 100,
            ])
            ->assertForbidden();
    }

    public function test_inventory_item_must_belong_to_same_company(): void
    {
        [$company, $branch, $user] = $this->userWithRole('Company Admin');
        $otherCompany = Company::factory()->create();
        $foreignItem = InventoryItem::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.ink-profiles.store'), [
                'name' => 'Invalid Link',
                'ink_type' => PrintInkType::Cmyk->value,
                'cartridge_cost' => 1000,
                'inventory_item_id' => $foreignItem->id,
                'active' => 1,
            ])
            ->assertSessionHasErrors('inventory_item_id');
    }

    public function test_cost_per_ml_is_derived_correctly(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Derived',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 5000,
            'estimated_ml' => 1000,
            'cost_per_ml' => null,
            'active' => true,
        ]);

        $this->assertEqualsWithDelta(5.0, app(InkCostProfileService::class)->costPerMl($profile), 0.0001);
        $this->assertEqualsWithDelta(5.0, app(PrintInkProfileManagementService::class)->previewCostPerMl($profile), 0.0001);
    }

    public function test_inactive_profiles_do_not_appear_in_ink_intelligence(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Inactive Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 1000,
            'estimated_ml' => 500,
            'active' => false,
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee(__('Ink estimation requires at least one active ink profile.'));
    }

    public function test_profile_used_by_estimates_is_deactivated_not_deleted(): void
    {
        [$company, $branch, $user] = $this->userWithRole('Company Admin');

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Used Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 1000,
            'estimated_ml' => 500,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'original_filename' => 'used.pdf',
            'stored_filename' => 'used.pdf',
            'file_path' => 'printing-intelligence/artwork/used.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size_bytes' => 1000,
            'file_hash' => hash('sha256', 'used-ink-profile'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'ink_profile_id' => $profile->id,
            'estimation_status' => \App\Enums\InkEstimationStatus::Completed,
            'formula_version' => 'PI3-V1',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->delete(route('admin.printing-intelligence.ink-profiles.destroy', $profile))
            ->assertRedirect();

        $profile->refresh();
        $this->assertFalse($profile->active);
        $this->assertDatabaseHas('print_ink_profiles', ['id' => $profile->id]);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWith(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWithRole(string $roleName): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($roleName);

        return [$company, $branch, $user];
    }
}
