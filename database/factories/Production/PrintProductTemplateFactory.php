<?php

namespace Database\Factories\Production;

use App\Enums\PrintProductTemplateCategory;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\PrintProductTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PrintProductTemplate>
 */
class PrintProductTemplateFactory extends Factory
{
    protected $model = PrintProductTemplate::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'code' => Str::upper(Str::slug($name, '')),
            'name' => Str::title($name),
            'category' => PrintProductTemplateCategory::Marketing,
            'description' => fake()->sentence(),
            'is_active' => true,
            'production_type' => ProductionType::Digital,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
