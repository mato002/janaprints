<?php

namespace Database\Factories\Crm;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Branch;
use App\Models\Crm\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $branch = Branch::factory();

        return [
            'company_id' => $branch,
            'branch_id' => $branch,
            'customer_code' => 'CUST-'.fake()->unique()->numerify('#####'),
            'customer_type' => CustomerType::Corporate,
            'company_name' => fake()->company(),
            'status' => CustomerStatus::Active,
            'credit_limit' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Customer $customer) {
            if ($customer->branch_id && ! $customer->company_id) {
                $customer->company_id = Branch::query()->find($customer->branch_id)?->company_id;
            }
        });
    }
}
