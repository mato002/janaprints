<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Support\Hr\LeaveTypeService;
use Illuminate\Database\Seeder;

class DefaultLeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(LeaveTypeService::class);

        Company::query()->each(function (Company $company) use ($service) {
            $service->seedDefaultsForCompany($company);
        });
    }
}
