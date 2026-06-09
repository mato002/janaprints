<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrWorkspaceEmbeddedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_hr_section_renders_embedded_content_frame_src(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.workspaces.hr.section', ['section' => 'people']))
            ->assertOk()
            ->assertSee(route('admin.hr.dashboard', ['embedded' => '1']), false);
    }

    public function test_hr_dashboard_embedded_response_includes_workspace_frame(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.hr.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('HR Dashboard'));
    }

    public function test_hr_dashboard_embedded_response_includes_workspace_frame_for_turbo_frame_request(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    public function test_employees_tab_embedded_response_includes_workspace_frame(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->get(route('admin.employees.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false)
            ->assertSee(__('Employees'));
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }
}
