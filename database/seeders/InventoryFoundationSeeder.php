<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class InventoryFoundationSeeder extends Seeder
{
    /** @var list<array{code: string, name: string}> */
    private array $units = [
        ['code' => 'SHEET', 'name' => 'Sheet'],
        ['code' => 'PIECE', 'name' => 'Piece'],
        ['code' => 'ROLL', 'name' => 'Roll'],
        ['code' => 'KG', 'name' => 'Kg'],
        ['code' => 'LITRE', 'name' => 'Litre'],
        ['code' => 'METER', 'name' => 'Meter'],
        ['code' => 'BOX', 'name' => 'Box'],
    ];

    /** @var list<array{code: string, name: string}> */
    private array $categories = [
        ['code' => 'PAPER', 'name' => 'Paper'],
        ['code' => 'INK', 'name' => 'Ink'],
        ['code' => 'PLATES', 'name' => 'Plates'],
        ['code' => 'CHEMICALS', 'name' => 'Chemicals'],
        ['code' => 'PACKAGING', 'name' => 'Packaging'],
        ['code' => 'FINISHED', 'name' => 'Finished Goods'],
        ['code' => 'SPARES', 'name' => 'Spare Parts'],
    ];

    /** @var list<array{code: string, name: string}> */
    private array $warehouses = [
        ['code' => 'MAIN', 'name' => 'Main Store'],
        ['code' => 'PAPER', 'name' => 'Paper Store'],
        ['code' => 'INK', 'name' => 'Ink Store'],
        ['code' => 'FG', 'name' => 'Finished Goods Store'],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('units_of_measure')) {
            return;
        }

        foreach (Company::query()->get() as $company) {
            foreach (Branch::query()->where('company_id', $company->id)->get() as $branch) {
                foreach ($this->units as $unit) {
                    UnitOfMeasure::query()->firstOrCreate(
                        ['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $unit['code']],
                        ['name' => $unit['name'], 'is_active' => true],
                    );
                }
                foreach ($this->categories as $cat) {
                    InventoryCategory::query()->firstOrCreate(
                        ['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $cat['code']],
                        ['name' => $cat['name'], 'is_active' => true],
                    );
                }
                foreach ($this->warehouses as $wh) {
                    Warehouse::query()->firstOrCreate(
                        ['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $wh['code']],
                        ['name' => $wh['name'], 'is_active' => true],
                    );
                }
            }
        }
    }
}
