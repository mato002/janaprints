<?php

namespace Database\Factories\Production;

use App\Enums\PrintInkType;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionSpecification>
 */
class ProductionSpecificationFactory extends Factory
{
    protected $model = ProductionSpecification::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'customer_id' => fn (array $attrs) => Customer::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'production_type' => ProductionType::Digital,
            'product_description' => fake()->sentence(4),
            'quantity' => fake()->numberBetween(100, 5000),
            'unit' => 'copies',
            'size' => 'A4',
            'finished_size' => 'A4',
            'sheet_size' => 'SRA3',
            'orientation' => 'portrait',
            'ink_type' => PrintInkType::Cmyk,
            'colour_mode' => 'cmyk',
            'sides' => 'double',
            'binding_type' => 'none',
            'finishing_type' => 'none',
            'ups' => 2,
            'estimated_sheets' => 250,
            'waste_allowance_percent' => 5,
            'approval_status' => ProductionSpecificationApprovalStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function forSalesOrderItem(SalesOrderItem $item): static
    {
        return $this->state(fn () => [
            'company_id' => $item->salesOrder?->company_id,
            'branch_id' => $item->salesOrder?->branch_id,
            'customer_id' => $item->salesOrder?->customer_id,
            'sales_order_id' => $item->sales_order_id,
            'sales_order_item_id' => $item->id,
            'product_description' => $item->description ?? $item->item_name,
            'quantity' => $item->quantity,
        ]);
    }

    public function forSalesOrder(SalesOrder $order): static
    {
        return $this->state(fn () => [
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
        ]);
    }
}
