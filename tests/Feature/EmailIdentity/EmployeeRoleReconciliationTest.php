<?php

namespace Tests\Feature\EmailIdentity;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmailIdentity\EmployeeRoleReconciliationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRoleReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_reconcile_fixes_mismatch_for_admin_preapproved_role(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-ROLE-01',
            'first_name' => 'Role',
            'last_name' => 'Fix',
            'email' => 'role.fix@example.com',
            'employment_status' => 'active',
            'activation_role' => 'Sales',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'email' => 'role.fix@example.com',
            'is_active' => true,
        ]);

        $user->syncRoles(['Viewer']);

        $summary = app(EmployeeRoleReconciliationService::class)->reconcile($company->id);

        $this->assertSame(1, $summary['fixed']);
        $this->assertTrue($user->fresh()->hasRole('Sales'));
    }

    public function test_reconcile_preserves_seeded_role_when_activation_role_missing(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-ROLE-02',
            'first_name' => 'Seeded',
            'last_name' => 'User',
            'email' => 'seeded.user@example.com',
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'email' => 'seeded.user@example.com',
            'is_active' => true,
        ]);

        $user->syncRoles(['Designer']);

        $summary = app(EmployeeRoleReconciliationService::class)->reconcile($company->id);

        $this->assertSame(1, $summary['ok']);
        $this->assertTrue($user->fresh()->hasRole('Designer'));
    }
}
