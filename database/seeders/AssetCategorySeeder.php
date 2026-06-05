<?php

namespace Database\Seeders;

use App\Enums\AssetType;
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
            ['name' => 'Printers', 'code' => 'PRINT', 'asset_type' => AssetType::Printer, 'default_gl_code' => '1520', 'useful_life_years' => 5],
            ['name' => 'Plotters', 'code' => 'PLOT', 'asset_type' => AssetType::Plotter, 'default_gl_code' => '1520', 'useful_life_years' => 7],
            ['name' => 'Digital Presses', 'code' => 'PRESS', 'asset_type' => AssetType::Machine, 'default_gl_code' => '1530', 'useful_life_years' => 6],
            ['name' => 'Cutters', 'code' => 'CUT', 'asset_type' => AssetType::Machine, 'default_gl_code' => '1530', 'useful_life_years' => 6],
            ['name' => 'Laminators', 'code' => 'LAM', 'asset_type' => AssetType::Machine, 'default_gl_code' => '1530', 'useful_life_years' => 6],
            ['name' => 'Vehicles', 'code' => 'VEH', 'asset_type' => AssetType::Vehicle, 'default_gl_code' => '1540', 'useful_life_years' => 5],
            ['name' => 'Computers', 'code' => 'IT', 'asset_type' => AssetType::Computer, 'default_gl_code' => '1510', 'useful_life_years' => 3],
            ['name' => 'Furniture', 'code' => 'FURN', 'asset_type' => AssetType::Furniture, 'default_gl_code' => '1510', 'useful_life_years' => 10],
            ['name' => 'Generators', 'code' => 'GEN', 'asset_type' => AssetType::Generator, 'default_gl_code' => '1530', 'useful_life_years' => 10],
            ['name' => 'Networking Equipment', 'code' => 'NET', 'asset_type' => AssetType::Network, 'default_gl_code' => '1510', 'useful_life_years' => 5],
            ['name' => 'Office Equipment', 'code' => 'OFF', 'asset_type' => AssetType::Office, 'default_gl_code' => '1510', 'useful_life_years' => 5],
            ['name' => 'Tools', 'code' => 'TOOL', 'asset_type' => AssetType::Tool, 'default_gl_code' => '1530', 'useful_life_years' => 4],
            ['name' => 'Leasehold Improvements', 'code' => 'LEASE', 'asset_type' => AssetType::Leasehold, 'default_gl_code' => '1530', 'useful_life_years' => 10],
            ['name' => 'Other Assets', 'code' => 'OTHER', 'asset_type' => AssetType::Other, 'default_gl_code' => '1530', 'useful_life_years' => 5],
        ];

        foreach ($categories as $cat) {
            $years = (int) $cat['useful_life_years'];

            AssetCategory::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $cat['code']],
                [
                    'company_id' => $company->id,
                    'name' => $cat['name'],
                    'asset_type' => $cat['asset_type']->value,
                    'default_gl_code' => $cat['default_gl_code'],
                    'useful_life_years' => $years,
                    'useful_life_months' => $years * 12,
                    'depreciation_method' => 'straight_line',
                    'is_active' => true,
                ],
            );
        }
    }
}
