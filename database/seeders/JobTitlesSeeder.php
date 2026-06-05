<?php

namespace Database\Seeders;

use App\Enums\JobTitleLevel;
use App\Models\Company;
use App\Models\Department;
use App\Models\JobTitle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobTitlesSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        $departments = Department::query()
            ->where('company_id', $company->id)
            ->get()
            ->keyBy('code');

        $definitions = [
            ['code' => 'MD', 'title' => 'Managing Director', 'level' => JobTitleLevel::Executive, 'department' => 'ADMIN', 'reports_to' => null, 'approval_authority' => 'Company Admin', 'sort_order' => 10],
            ['code' => 'OPS_MGR', 'title' => 'Operations Manager', 'level' => JobTitleLevel::SeniorManagement, 'department' => 'ADMIN', 'reports_to' => 'MD', 'approval_authority' => 'Branch Manager', 'sort_order' => 20],
            ['code' => 'FIN_MGR', 'title' => 'Finance Manager', 'level' => JobTitleLevel::Management, 'department' => 'ADMIN', 'reports_to' => 'MD', 'approval_authority' => 'Company Admin', 'sort_order' => 30],
            ['code' => 'COM_MGR', 'title' => 'Commercial Manager', 'level' => JobTitleLevel::Management, 'department' => 'ADMIN', 'reports_to' => 'OPS_MGR', 'approval_authority' => 'Branch Manager', 'sort_order' => 40],
            ['code' => 'PROD_MGR', 'title' => 'Production Manager', 'level' => JobTitleLevel::Management, 'department' => 'ADMIN', 'reports_to' => 'OPS_MGR', 'approval_authority' => 'Branch Manager', 'sort_order' => 50],
            ['code' => 'SCM_MGR', 'title' => 'Supply Chain Manager', 'level' => JobTitleLevel::Management, 'department' => 'ADMIN', 'reports_to' => 'OPS_MGR', 'approval_authority' => 'Storekeeper', 'sort_order' => 60],
            ['code' => 'STOREKEEPER', 'title' => 'Storekeeper', 'level' => JobTitleLevel::Supervisor, 'department' => 'ADMIN', 'reports_to' => 'SCM_MGR', 'approval_authority' => 'Storekeeper', 'sort_order' => 70],
            ['code' => 'GRAPHIC_DES', 'title' => 'Graphic Designer', 'level' => JobTitleLevel::Officer, 'department' => 'ADMIN', 'reports_to' => 'PROD_MGR', 'approval_authority' => 'Designer', 'sort_order' => 80],
            ['code' => 'MACHINE_OP', 'title' => 'Machine Operator', 'level' => JobTitleLevel::Staff, 'department' => 'ADMIN', 'reports_to' => 'PROD_MGR', 'approval_authority' => null, 'sort_order' => 90],
            ['code' => 'SALES_EXEC', 'title' => 'Sales Executive', 'level' => JobTitleLevel::Officer, 'department' => 'ADMIN', 'reports_to' => 'COM_MGR', 'approval_authority' => 'Sales', 'sort_order' => 100],
            ['code' => 'CASHIER', 'title' => 'Cashier', 'level' => JobTitleLevel::Staff, 'department' => 'ADMIN', 'reports_to' => 'COM_MGR', 'approval_authority' => null, 'sort_order' => 110],
            ['code' => 'RECEPTIONIST', 'title' => 'Receptionist', 'level' => JobTitleLevel::Staff, 'department' => 'ADMIN', 'reports_to' => 'COM_MGR', 'approval_authority' => null, 'sort_order' => 120],
            ['code' => 'HR_OFFICER', 'title' => 'HR Officer', 'level' => JobTitleLevel::Officer, 'department' => 'ADMIN', 'reports_to' => 'OPS_MGR', 'approval_authority' => null, 'sort_order' => 130],
            ['code' => 'BRANCH_MGR', 'title' => 'Branch Manager', 'level' => JobTitleLevel::Management, 'department' => 'ADMIN', 'reports_to' => 'MD', 'approval_authority' => 'Branch Manager', 'sort_order' => 25],
            ['code' => 'COMPANY_ADMIN', 'title' => 'Company Administrator', 'level' => JobTitleLevel::SeniorManagement, 'department' => 'ADMIN', 'reports_to' => 'MD', 'approval_authority' => 'Company Admin', 'sort_order' => 15],
            ['code' => 'SALES_REP', 'title' => 'Sales Representative', 'level' => JobTitleLevel::Officer, 'department' => 'ADMIN', 'reports_to' => 'COM_MGR', 'approval_authority' => 'Sales', 'sort_order' => 105],
            ['code' => 'SUPER_ADMIN', 'title' => 'Super Administrator', 'level' => JobTitleLevel::Executive, 'department' => 'ADMIN', 'reports_to' => null, 'approval_authority' => 'Super Admin', 'sort_order' => 5],
        ];

        $created = [];

        foreach ($definitions as $definition) {
            $department = $departments->get($definition['department']);

            $created[$definition['code']] = JobTitle::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $definition['code']],
                [
                    'title' => $definition['title'],
                    'department_id' => $department?->id,
                    'description' => __('Standard organizational job title.'),
                    'level' => $definition['level'],
                    'sort_order' => $definition['sort_order'],
                    'approval_authority' => $definition['approval_authority'],
                    'is_active' => true,
                ],
            );
        }

        foreach ($definitions as $definition) {
            if (blank($definition['reports_to'])) {
                continue;
            }

            $title = $created[$definition['code']] ?? null;
            $parent = $created[$definition['reports_to']] ?? null;

            if ($title && $parent) {
                $title->update(['reports_to_job_title_id' => $parent->id]);
            }
        }
    }
}
