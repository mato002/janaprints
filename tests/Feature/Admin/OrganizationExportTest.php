<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Company Admin', 'web')->syncPermissions($permissions);
        $user->assignRole('Company Admin');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    public function test_organization_exports_return_real_files(): void
    {
        [$company, $branch, $user] = $this->tenantContext([
            'companies.manage',
            'branches.manage',
            'departments.manage',
            'employees.manage',
            'organization.job_titles.view',
            'roles.view',
        ]);

        $routes = [
            'admin.companies.export',
            'admin.branches.export',
            'admin.departments.export',
            'admin.employees.export',
            'admin.job-titles.export',
            'admin.roles.export',
        ];

        foreach ($routes as $routeName) {
            foreach (['csv', 'excel', 'pdf'] as $format) {
                $response = $this->actingAs($user)->get(route($routeName, ['format' => $format]));

                $response->assertOk()->assertHeader('content-disposition');

                if ($format === 'pdf') {
                    $response->assertHeader('content-type', 'application/pdf');
                    $this->assertStringStartsWith('%PDF', $response->streamedContent());
                }
            }
        }
    }
}
