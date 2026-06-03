<?php

namespace Database\Factories\Sales;

use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $branch = Branch::factory();

        return [
            'company_id' => $branch,
            'branch_id' => $branch,
            'customer_id' => Customer::factory(),
            'quotation_number' => 'QUO-'.fake()->unique()->numerify('#####'),
            'quotation_date' => now()->toDateString(),
            'currency' => 'KES',
            'subtotal' => 1000,
            'tax_amount' => 160,
            'discount_amount' => 0,
            'total_amount' => 1160,
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
            'prepared_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Quotation $quotation) {
            if ($quotation->branch_id && ! $quotation->company_id) {
                $quotation->company_id = Branch::query()->find($quotation->branch_id)?->company_id;
            }
        });
    }
}
