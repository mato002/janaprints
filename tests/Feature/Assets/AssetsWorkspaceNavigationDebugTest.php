<?php

namespace Tests\Feature\Assets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetsWorkspaceNavigationDebugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_assets_hub_loads_without_redirect_loop(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->followingRedirects()
            ->get(route('admin.workspaces.assets'))
            ->assertOk()
            ->assertSee(__('Asset Management'), false)
            ->assertSee(route('admin.assets.index', ['embedded' => '1']), false);
    }

    public function test_assets_hub_does_not_redirect_to_supply_chain_shell(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.assets'));

        if ($response->isRedirect()) {
            $location = (string) $response->headers->get('Location');
            $this->assertStringNotContainsString('/admin/workspaces/supply-chain/assets', $location);
            $this->assertStringContainsString('/admin/workspaces/assets', $location);
        } else {
            $response->assertOk();
        }
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
