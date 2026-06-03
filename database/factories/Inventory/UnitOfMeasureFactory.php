<?php

namespace Database\Factories\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitOfMeasure>
 */
class UnitOfMeasureFactory extends Factory
{
    protected $model = UnitOfMeasure::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'name' => fake()->word(),
            'is_active' => true,
        ];
    }
}
