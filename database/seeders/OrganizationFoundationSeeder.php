<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class OrganizationFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->firstOrCreate(
            ['code' => 'JANA'],
            [
                'name' => 'Jana Prints',
                'email' => 'info@janaprints.local',
                'phone' => null,
                'address' => null,
                'logo' => null,
                'settings_json' => [],
                'is_active' => true,
            ],
        );

        Branch::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'HQ'],
            [
                'name' => 'Head Office',
                'email' => null,
                'phone' => null,
                'address' => null,
                'is_head_office' => true,
                'is_active' => true,
            ],
        );

        Department::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ADMIN'],
            [
                'name' => 'Administration',
                'description' => 'System administration and management',
                'is_active' => true,
            ],
        );
    }
}
