<?php

namespace Tests\Feature\Artwork;

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Artwork\DesignerOperatorMode;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DesignerOperatorModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_designer_role_user_prefers_operator_mode_but_admins_do_not(): void
    {
        $designer = $this->userWithRole('Designer');
        $admin = $this->userWithRole('Company Admin');

        $this->assertTrue(DesignerOperatorMode::enabledFor($designer));
        $this->assertTrue($designer->prefersDesignerOperatorMode());
        $this->assertFalse(DesignerOperatorMode::enabledFor($admin));
        $this->assertFalse($admin->prefersDesignerOperatorMode());
    }

    public function test_designer_operator_login_lands_on_desk(): void
    {
        $designer = $this->userWithRole('Designer');

        $this->post(route('admin.login'), [
            'email' => $designer->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.artwork.desk'));
    }

    public function test_designer_commercial_workspace_redirects_to_desk(): void
    {
        $designer = $this->userWithRole('Designer');

        $this->actingAs($designer)
            ->get(route('admin.workspaces.commercial'))
            ->assertRedirect(route('admin.artwork.desk'));

        $this->actingAs($designer)
            ->get(route('admin.workspaces.commercial.section', ['section' => 'sales']))
            ->assertRedirect(route('admin.artwork.desk'));
    }

    public function test_designer_artwork_list_and_dashboard_redirect_to_desk(): void
    {
        $designer = $this->userWithRole('Designer');

        $this->actingAs($designer)
            ->get(route('admin.artwork.index'))
            ->assertRedirect(route('admin.artwork.desk'));

        $this->actingAs($designer)
            ->get(route('admin.artwork.dashboard'))
            ->assertRedirect(route('admin.artwork.desk'));
    }

    public function test_designer_dashboard_redirects_to_desk(): void
    {
        $designer = $this->userWithRole('Designer');

        $this->actingAs($designer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.artwork.desk'));
    }

    public function test_designer_can_open_desk_and_return_from_request_show(): void
    {
        $designer = $this->userWithRole('Designer');
        $companyId = $designer->company_id;
        $branchId = $designer->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-DESK-001',
            'title' => 'Desk Banner',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::InDesign,
            'assigned_designer_id' => $designer->id,
            'requested_by' => $designer->id,
            'current_version' => 0,
        ]);

        $modalUrl = route('admin.artwork.show', [$request, 'from' => 'designer-desk']);

        $this->actingAs($designer)
            ->get(route('admin.artwork.desk'))
            ->assertOk()
            ->assertSee(__('Designer desk'))
            ->assertSee('AW-DESK-001')
            ->assertSee('data-erp-modal-open', false)
            ->assertSee('from=designer-desk', false);

        $this->actingAs($designer)
            ->withHeaders(['Turbo-Frame' => 'erp-form-modal'])
            ->get($modalUrl)
            ->assertOk()
            ->assertSee('data-erp-form-modal-panel', false)
            ->assertSee(__('Submit for approval'), false)
            ->assertSee(__('Upload version'), false);

        $this->actingAs($designer)
            ->get(route('admin.artwork.show', $request))
            ->assertOk()
            ->assertSee(__('Back to Designer Desk'))
            ->assertSee(route('admin.artwork.desk'), false);
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }
}
