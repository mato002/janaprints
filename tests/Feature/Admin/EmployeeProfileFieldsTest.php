<?php

namespace Tests\Feature\Admin;

use App\Enums\EmploymentStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Support\Hr\EmployeeProfileCompletenessService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeProfileFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
    }

    public function test_employee_edit_form_includes_statutory_and_bank_fields(): void
    {
        $employee = $this->employee('EMP-PROFILE-01');

        $this->actingAs($this->hrUser())
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->get(route('admin.employees.edit', $employee))
            ->assertOk()
            ->assertSee('name="kra_pin"', false)
            ->assertSee('name="nssf_number"', false)
            ->assertSee('name="nhif_number"', false)
            ->assertSee('name="bank_name"', false)
            ->assertSee('name="bank_account_number"', false);
    }

    public function test_employee_update_persists_corporate_profile_fields(): void
    {
        $employee = $this->employee('EMP-PROFILE-02');
        $hr = $this->hrUser();

        $this->actingAs($hr)
            ->put(route('admin.employees.update', $employee), [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'employee_number' => $employee->employee_number,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'employment_status' => EmploymentStatus::Active->value,
                'is_active' => true,
                'kra_pin' => 'A123456789Z',
                'nssf_number' => 'NS-123456',
                'nhif_number' => 'SHIF-998877',
                'bank_name' => 'Equity Bank',
                'bank_account_number' => '0123456789',
                'bank_branch_code' => '068',
                'national_id' => '12345678',
                'address' => 'Nairobi, Kenya',
            ])
            ->assertRedirect(route('admin.employees.index'));

        $employee->refresh();

        $this->assertSame('A123456789Z', $employee->kra_pin);
        $this->assertSame('NS-123456', $employee->nssf_number);
        $this->assertSame('SHIF-998877', $employee->nhif_number);
        $this->assertSame('Equity Bank', $employee->bank_name);
        $this->assertSame('0123456789', $employee->bank_account_number);
        $this->assertTrue(app(EmployeeProfileCompletenessService::class)->isPayrollReady($employee));
    }

    public function test_profile_completeness_flags_missing_payroll_fields(): void
    {
        $employee = $this->employee('EMP-PROFILE-03');

        $missing = app(EmployeeProfileCompletenessService::class)->missingForPayroll($employee);

        $this->assertNotEmpty($missing);
        $this->assertFalse(app(EmployeeProfileCompletenessService::class)->isPayrollReady($employee));
    }

    protected function employee(string $number): Employee
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $department->id,
            'employee_number' => $number,
            'first_name' => 'Profile',
            'last_name' => 'Test',
            'email' => strtolower($number).'@janaprints.test',
            'employment_status' => EmploymentStatus::Active,
            'is_active' => true,
        ]);
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }
}
