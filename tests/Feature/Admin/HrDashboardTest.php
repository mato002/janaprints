<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_hr_dashboard_renders_for_hr_user(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.dashboard'))
            ->assertOk()
            ->assertSee(__('HR Dashboard'))
            ->assertSee(__('Attendance'))
            ->assertSee(__('Leave'))
            ->assertSee(__('Payroll'))
            ->assertSee(__('Documents'))
            ->assertSee(__('Performance'))
            ->assertSee(__('Training'))
            ->assertSee(__('Exit Management'));
    }

    public function test_viewer_cannot_access_hr_dashboard(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $this->actingAs($viewer)
            ->get(route('admin.hr.dashboard'))
            ->assertForbidden();
    }

    public function test_workspace_links_to_hr_dashboard(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.workspaces.hr'))
            ->assertOk()
            ->assertSee(__('HR Dashboard'));
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
