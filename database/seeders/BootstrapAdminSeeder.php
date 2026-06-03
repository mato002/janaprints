<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BootstrapAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $name || ! $email || ! $password) {
            $this->command?->warn('Bootstrap admin skipped: set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD in .env');

            return;
        }

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->firstOrFail();
        $department = Department::query()
            ->where('company_id', $company->id)
            ->where('code', 'ADMIN')
            ->firstOrFail();

        $employee = Employee::query()->firstOrCreate(
            ['company_id' => $company->id, 'employee_number' => 'EMP-0001'],
            [
                'branch_id' => $branch->id,
                'department_id' => $department->id,
                'first_name' => explode(' ', $name, 2)[0],
                'middle_name' => null,
                'last_name' => explode(' ', $name, 2)[1] ?? 'Administrator',
                'gender' => Gender::Other,
                'email' => $email,
                'designation' => 'Super Administrator',
                'hire_date' => now()->toDateString(),
                'employment_status' => EmploymentStatus::Active,
                'is_active' => true,
            ],
        );

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'company_id' => $company->id,
                'default_branch_id' => $branch->id,
                'employee_id' => $employee->id,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles(['Super Admin']);
    }
}
