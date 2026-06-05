<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Support\Hr\ShiftService;
use Illuminate\Database\Seeder;

class DefaultShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ShiftService::class);

        Company::query()->each(function (Company $company) use ($service) {
            $service->seedDefaultsForCompany($company);
        });
    }
}
