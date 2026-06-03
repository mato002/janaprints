<?php

namespace Database\Factories\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionJobCard>
 */
class ProductionJobCardFactory extends Factory
{
    protected $model = ProductionJobCard::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'sales_order_id' => fn (array $attrs) => SalesOrder::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'customer_id' => fn (array $attrs) => Customer::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'quotation_id' => fn (array $attrs) => SalesOrder::find($attrs['sales_order_id'])?->quotation_id,
            'artwork_request_id' => fn (array $attrs) => SalesOrder::find($attrs['sales_order_id'])?->artwork_request_id,
            'job_card_number' => 'JC-'.fake()->unique()->numerify('#####'),
            'production_type' => ProductionType::Mixed,
            'priority' => ProductionPriority::Normal,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => User::factory(),
        ];
    }
}
