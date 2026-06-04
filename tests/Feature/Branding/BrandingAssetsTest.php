<?php

namespace Tests\Feature\Branding;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Branding\BrandingAssets;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandingAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        Storage::fake(BrandingAssets::DISK);
    }

    public function test_guest_cannot_access_branding_settings(): void
    {
        $this->get(route('admin.settings.branding.edit'))->assertRedirect(route('login'));
    }

    public function test_user_with_manage_permission_can_upload_company_branding(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $logo = UploadedFile::fake()->image('logo.png', 64, 64);
        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        $this->actingAs($user)
            ->put(route('admin.settings.branding.update'), [
                'logo' => $logo,
                'favicon' => $favicon,
            ])
            ->assertRedirect(route('admin.settings.branding.edit'));

        $company->refresh();

        $this->assertNotNull($company->logo);
        $this->assertNotNull($company->favicon_path);
        Storage::disk(BrandingAssets::DISK)->assertExists($company->logo);
        Storage::disk(BrandingAssets::DISK)->assertExists($company->favicon_path);
    }

    public function test_user_can_upload_profile_avatar(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $avatar = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $avatar,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk(BrandingAssets::DISK)->assertExists($user->avatar_path);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Branding Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
