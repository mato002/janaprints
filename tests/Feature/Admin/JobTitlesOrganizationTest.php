<?php

namespace Tests\Feature\Admin;

use App\Enums\JobTitleLevel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use App\Support\Organization\JobTitleService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobTitlesOrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_index_renders_seeded_job_titles(): void
    {
        $admin = $this->orgAdmin();

        $this->actingAs($admin)
            ->get(route('admin.job-titles.index'))
            ->assertOk()
            ->assertSee(__('Job Titles'))
            ->assertSee(__('Commercial Manager'))
            ->assertSee(__('Sales Executive'));
    }

    public function test_create_job_title(): void
    {
        $admin = $this->orgAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.job-titles.store'), [
                'company_id' => $company->id,
                'code' => 'QA_OFFICER',
                'title' => 'Quality Assurance Officer',
                'department_id' => $department->id,
                'level' => JobTitleLevel::Officer->value,
                'sort_order' => 200,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.job-titles.index'));

        $this->assertDatabaseHas('job_titles', [
            'company_id' => $company->id,
            'code' => 'QA_OFFICER',
            'title' => 'Quality Assurance Officer',
        ]);
    }

    public function test_edit_job_title(): void
    {
        $admin = $this->orgAdmin();
        $title = JobTitle::query()->where('code', 'CASHIER')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.job-titles.update', $title), [
                'company_id' => $title->company_id,
                'code' => $title->code,
                'title' => 'Senior Cashier',
                'department_id' => $title->department_id,
                'level' => $title->level->value,
                'sort_order' => $title->sort_order,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.job-titles.index'));

        $this->assertSame('Senior Cashier', $title->fresh()->title);
    }

    public function test_deactivate_job_title_without_employees(): void
    {
        $admin = $this->orgAdmin();
        $title = JobTitle::query()->where('code', 'RECEPTIONIST')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.job-titles.deactivate', $title))
            ->assertRedirect(route('admin.job-titles.index'));

        $this->assertFalse($title->fresh()->is_active);
    }

    public function test_assign_employee_to_job_title(): void
    {
        $admin = $this->orgAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();
        $title = JobTitle::query()->where('code', 'SALES_EXEC')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'department_id' => $department->id,
                'employee_number' => 'EMP-TEST-01',
                'first_name' => 'Test',
                'last_name' => 'Seller',
                'job_title_id' => $title->id,
                'employment_status' => 'active',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $employee = Employee::query()->where('employee_number', 'EMP-TEST-01')->firstOrFail();
        $this->assertSame($title->id, $employee->job_title_id);
        $this->assertSame('Sales Executive', $employee->designation);
    }

    public function test_reporting_structure_links_titles(): void
    {
        $salesExecutive = JobTitle::query()->where('code', 'SALES_EXEC')->firstOrFail();
        $commercialManager = JobTitle::query()->where('code', 'COM_MGR')->firstOrFail();

        $this->assertSame($commercialManager->id, $salesExecutive->reports_to_job_title_id);
    }

    public function test_hierarchy_renders_reporting_lines(): void
    {
        $admin = $this->orgAdmin();

        $this->actingAs($admin)
            ->get(route('admin.job-titles.hierarchy'))
            ->assertOk()
            ->assertSee(__('Organization Chart'))
            ->assertSee(__('Sales Executive'))
            ->assertSee(__('Commercial Manager'));
    }

    public function test_hierarchy_service_builds_tree(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $tree = app(JobTitleService::class)->hierarchyTree($company->id);

        $this->assertNotEmpty($tree['nodes']);
        $this->assertGreaterThan(0, $tree['branches']->count());
    }

    public function test_permission_enforcement_blocks_access(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.job-titles.index'))
            ->assertForbidden();
    }

    public function test_organization_workspace_links_job_titles(): void
    {
        $admin = $this->orgAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'organization']))
            ->assertOk()
            ->assertSee(route('admin.job-titles.index'), false)
            ->assertSee(__('Job Titles'));
    }

    protected function orgAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findOrCreate('Org Admin', 'web');
        foreach ([
            'organization.job_titles.view',
            'organization.job_titles.create',
            'organization.job_titles.edit',
            'organization.job_titles.deactivate',
            'employees.manage',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $role->syncPermissions([
            'organization.job_titles.view',
            'organization.job_titles.create',
            'organization.job_titles.edit',
            'organization.job_titles.deactivate',
            'employees.manage',
        ]);
        $user->assignRole($role);

        return $user;
    }
}
