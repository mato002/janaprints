<?php

namespace Database\Factories\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'inventory_category_id' => fn (array $attrs) => InventoryCategory::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'unit_of_measure_id' => fn (array $attrs) => UnitOfMeasure::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'item_name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'reorder_level' => 10,
            'reorder_quantity' => 50,
            'standard_cost' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
