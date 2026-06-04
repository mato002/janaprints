<?php

namespace Database\Seeders;

use App\Models\Assets\AssetCategory;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        $categories = [
            ['name' => 'Printers', 'code' => 'PRINT', 'default_gl_code' => '1520', 'useful_life_months' => 60],
            ['name' => 'Plotters', 'code' => 'PLOT', 'default_gl_code' => '1520', 'useful_life_months' => 84],
            ['name' => 'Cutters', 'code' => 'CUT', 'default_gl_code' => '1530', 'useful_life_months' => 72],
            ['name' => 'Laminators', 'code' => 'LAM', 'default_gl_code' => '1530', 'useful_life_months' => 72],
            ['name' => 'Vehicles', 'code' => 'VEH', 'default_gl_code' => '1540', 'useful_life_months' => 60],
            ['name' => 'Computers', 'code' => 'IT', 'default_gl_code' => '1510', 'useful_life_months' => 36],
            ['name' => 'Furniture', 'code' => 'FURN', 'default_gl_code' => '1510', 'useful_life_months' => 120],
            ['name' => 'Generators', 'code' => 'GEN', 'default_gl_code' => '1530', 'useful_life_months' => 120],
        ];

        foreach ($categories as $cat) {
            AssetCategory::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $cat['code']],
                array_merge($cat, ['company_id' => $company->id, 'is_active' => true]),
            );
        }
    }
}
