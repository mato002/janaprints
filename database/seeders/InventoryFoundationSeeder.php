<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\ItemAttribute;
use App\Models\Inventory\PriceList;
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
        ['code' => 'PACK', 'name' => 'Pack'],
    ];

    /** @var list<array{code: string, name: string}> */
    private array $categories = [
        ['code' => 'PAPER', 'name' => 'Paper'],
        ['code' => 'INK', 'name' => 'Ink'],
        ['code' => 'BANNER', 'name' => 'Banner Material'],
        ['code' => 'VINYL', 'name' => 'Vinyl'],
        ['code' => 'TSHIRT', 'name' => 'T-Shirts'],
        ['code' => 'BCARD', 'name' => 'Business Cards'],
        ['code' => 'FLYER', 'name' => 'Flyers'],
        ['code' => 'BROCHURE', 'name' => 'Brochures'],
        ['code' => 'STICKER', 'name' => 'Stickers'],
        ['code' => 'RECEIPT', 'name' => 'Receipt Books'],
        ['code' => 'GENERAL', 'name' => 'General Supplies'],
        ['code' => 'PACKAGING', 'name' => 'Packaging Materials'],
        ['code' => 'FINISHING', 'name' => 'Finishing Materials'],
    ];

    /** @var array<string, list<array{code: string, name: string}>> */
    private array $subcategories = [
        'PAPER' => [
            ['code' => 'ART', 'name' => 'Art Paper'],
            ['code' => 'GLOSS', 'name' => 'Gloss Paper'],
            ['code' => 'MATTE', 'name' => 'Matte Paper'],
            ['code' => 'BOND', 'name' => 'Bond Paper'],
            ['code' => 'NCR', 'name' => 'NCR Paper'],
            ['code' => 'KRAFT', 'name' => 'Kraft Paper'],
            ['code' => 'STICKER', 'name' => 'Sticker Paper'],
        ],
        'INK' => [
            ['code' => 'CMYK', 'name' => 'CMYK'],
            ['code' => 'UV', 'name' => 'UV Ink'],
            ['code' => 'SOLVENT', 'name' => 'Solvent Ink'],
            ['code' => 'ECO', 'name' => 'Eco Solvent'],
            ['code' => 'SUB', 'name' => 'Sublimation Ink'],
        ],
        'BANNER' => [
            ['code' => 'FRONTLIT', 'name' => 'Frontlit'],
            ['code' => 'BACKLIT', 'name' => 'Backlit'],
            ['code' => 'MESH', 'name' => 'Mesh Banner'],
        ],
        'VINYL' => [
            ['code' => 'GLOSS', 'name' => 'Gloss Vinyl'],
            ['code' => 'MATTE', 'name' => 'Matte Vinyl'],
            ['code' => 'REFLECT', 'name' => 'Reflective Vinyl'],
        ],
        'TSHIRT' => [
            ['code' => 'COTTON', 'name' => 'Cotton'],
            ['code' => 'POLY', 'name' => 'Polyester'],
            ['code' => 'BRANDED', 'name' => 'Branded'],
        ],
    ];

    /** @var list<array{code: string, name: string}> */
    private array $brands = [
        ['code' => 'CANON', 'name' => 'Canon'],
        ['code' => 'EPSON', 'name' => 'Epson'],
        ['code' => 'HP', 'name' => 'HP'],
        ['code' => 'ROLAND', 'name' => 'Roland'],
        ['code' => 'MIMAKI', 'name' => 'Mimaki'],
        ['code' => 'AVERY', 'name' => 'Avery'],
        ['code' => '3M', 'name' => '3M'],
        ['code' => 'GENERIC', 'name' => 'Generic'],
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
                        ['name' => $cat['name'], 'description' => $cat['name'], 'is_active' => true],
                    );
                }
                if (Schema::hasTable('inventory_subcategories')) {
                    foreach ($this->subcategories as $categoryCode => $subcategories) {
                        $category = InventoryCategory::query()
                            ->where('company_id', $company->id)
                            ->where('branch_id', $branch->id)
                            ->where('code', $categoryCode)
                            ->first();

                        if (! $category) {
                            continue;
                        }

                        foreach ($subcategories as $sub) {
                            InventorySubcategory::query()->firstOrCreate(
                                ['company_id' => $company->id, 'branch_id' => $branch->id, 'inventory_category_id' => $category->id, 'code' => $sub['code']],
                                ['name' => $sub['name'], 'is_active' => true],
                            );
                        }
                    }
                }
                if (Schema::hasTable('brands')) {
                    foreach ($this->brands as $brand) {
                        Brand::query()->firstOrCreate(
                            ['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $brand['code']],
                            ['name' => $brand['name'], 'is_active' => true],
                        );
                    }
                }
                if (Schema::hasTable('item_attributes')) {
                    foreach ([
                        'GSM' => ['GSM', 'number', 'PAPER'],
                        'SIZE' => ['Size', 'text', null],
                        'FINISH' => ['Finish', 'text', null],
                        'COLOR' => ['Color', 'text', null],
                        'VOLUME' => ['Volume', 'text', 'INK'],
                        'WIDTH' => ['Width', 'number', 'BANNER'],
                        'LENGTH' => ['Length', 'number', 'BANNER'],
                        'MATERIAL' => ['Material', 'text', 'TSHIRT'],
                    ] as $code => [$name, $type, $categoryCode]) {
                        $category = $categoryCode
                            ? InventoryCategory::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('code', $categoryCode)->first()
                            : null;

                        ItemAttribute::query()->firstOrCreate(
                            ['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $code],
                            ['name' => $name, 'data_type' => $type, 'inventory_category_id' => $category?->id, 'is_active' => true],
                        );
                    }
                }
                if (Schema::hasTable('price_lists')) {
                    foreach (['Retail Price List', 'Wholesale Price List', 'Corporate Price List', 'Government Price List'] as $name) {
                        PriceList::query()->firstOrCreate(
                            ['company_id' => $company->id, 'branch_id' => $branch->id, 'name' => $name],
                            ['currency' => 'KES', 'status' => 'active'],
                        );
                    }
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
