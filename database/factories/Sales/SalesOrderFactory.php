<?php

namespace Database\Factories\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'customer_id' => fn (array $attrs) => Customer::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'quotation_id' => fn (array $attrs) => Quotation::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
                'customer_id' => $attrs['customer_id'],
            ])->id,
            'artwork_request_id' => fn (array $attrs) => ArtworkRequest::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
                'customer_id' => $attrs['customer_id'],
                'quotation_id' => $attrs['quotation_id'],
            ])->id,
            'order_number' => 'SO-'.fake()->unique()->numerify('#####'),
            'order_date' => now()->toDateString(),
            'required_date' => null,
            'status' => SalesOrderStatus::Draft,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
