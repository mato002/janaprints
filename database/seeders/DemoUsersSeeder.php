<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    /**
     * @var list<array{
     *     employee_number: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     designation: string,
     *     role: string,
     * }>
     */
    private array $users = [
        [
            'employee_number' => 'EMP-0001',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@janaprints.local',
            'designation' => 'Super Administrator',
            'role' => 'Super Admin',
        ],
        [
            'employee_number' => 'EMP-0002',
            'first_name' => 'Jane',
            'last_name' => 'Mwangi',
            'email' => 'company.admin@janaprints.local',
            'designation' => 'Company Administrator',
            'role' => 'Company Admin',
        ],
        [
            'employee_number' => 'EMP-0003',
            'first_name' => 'Peter',
            'last_name' => 'Ochieng',
            'email' => 'branch.manager@janaprints.local',
            'designation' => 'Branch Manager',
            'role' => 'Branch Manager',
        ],
        [
            'employee_number' => 'EMP-0004',
            'first_name' => 'Grace',
            'last_name' => 'Wanjiku',
            'email' => 'sales@janaprints.local',
            'designation' => 'Sales Representative',
            'role' => 'Sales',
        ],
    ];

    public function run(): void
    {
        $password = env('DEMO_USER_PASSWORD', 'password');

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->firstOrFail();
        $department = Department::query()
            ->where('company_id', $company->id)
            ->where('code', 'ADMIN')
            ->firstOrFail();

        foreach ($this->users as $seedUser) {
            $name = trim("{$seedUser['first_name']} {$seedUser['last_name']}");

            $jobTitle = JobTitle::query()
                ->where('company_id', $company->id)
                ->where('title', $seedUser['designation'])
                ->first();

            $employee = Employee::query()->firstOrCreate(
                ['company_id' => $company->id, 'employee_number' => $seedUser['employee_number']],
                [
                    'branch_id' => $branch->id,
                    'department_id' => $department->id,
                    'job_title_id' => $jobTitle?->id,
                    'first_name' => $seedUser['first_name'],
                    'middle_name' => null,
                    'last_name' => $seedUser['last_name'],
                    'gender' => Gender::Other,
                    'email' => $seedUser['email'],
                    'designation' => $seedUser['designation'],
                    'hire_date' => now()->toDateString(),
                    'employment_status' => EmploymentStatus::Active,
                    'is_active' => true,
                ],
            );

            if ($jobTitle && $employee->job_title_id !== $jobTitle->id) {
                $employee->update(['job_title_id' => $jobTitle->id]);
            }

            $user = User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $name,
                    'password' => $password,
                    'company_id' => $company->id,
                    'default_branch_id' => $branch->id,
                    'employee_id' => $employee->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$seedUser['role']]);
        }
    }
}
