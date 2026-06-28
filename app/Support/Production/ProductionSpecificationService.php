<?php

namespace App\Support\Production;

use App\Enums\PrintInkType;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionSpecificationService
{
    public function __construct(
        protected ProductionSpecificationPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validationRules(?ProductionSpecification $existing = null): array
    {
        return [
            'production_type' => ['nullable', Rule::enum(ProductionType::class)],
            'product_description' => ['nullable', 'string', 'max:500'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:40'],
            'size' => ['nullable', 'string', 'max:80'],
            'finished_size' => ['nullable', 'string', 'max:80'],
            'sheet_size' => ['nullable', 'string', 'max:80'],
            'orientation' => ['nullable', 'string', 'max:20'],
            'paper_inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'material_inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'ink_type' => ['nullable', Rule::enum(PrintInkType::class)],
            'ink_profile_id' => ['nullable', 'integer', 'exists:print_ink_profiles,id'],
            'colour_mode' => ['nullable', 'string', 'max:40'],
            'sides' => ['nullable', 'string', 'max:20'],
            'binding_type' => ['nullable', 'string', 'max:60'],
            'finishing_type' => ['nullable', 'string', 'max:60'],
            'lamination' => ['nullable', 'boolean'],
            'foiling' => ['nullable', 'boolean'],
            'spot_uv' => ['nullable', 'boolean'],
            'embossing' => ['nullable', 'boolean'],
            'debossing' => ['nullable', 'boolean'],
            'die_cutting' => ['nullable', 'boolean'],
            'creasing' => ['nullable', 'boolean'],
            'perforation' => ['nullable', 'boolean'],
            'numbering_required' => ['nullable', 'boolean'],
            'eyelets' => ['nullable', 'boolean'],
            'ups' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'estimated_sheets' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'waste_allowance_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'artwork_reference' => ['nullable', 'string', 'max:120'],
            'artwork_version' => ['nullable', 'string', 'max:40'],
            'production_notes' => ['nullable', 'string', 'max:10000'],
            'delivery_notes' => ['nullable', 'string', 'max:10000'],
            'approval_status' => ['nullable', Rule::enum(ProductionSpecificationApprovalStatus::class)],
            'print_product_template_id' => ['nullable', 'integer', 'exists:print_product_templates,id'],
        ];
    }

    public function findForSalesOrderItem(SalesOrderItem $item): ?ProductionSpecification
    {
        return ProductionSpecification::query()
            ->where('sales_order_item_id', $item->id)
            ->first();
    }

    public function findForJobCard(ProductionJobCard $jobCard): ?ProductionSpecification
    {
        if ($jobCard->relationLoaded('productionSpecification') && $jobCard->productionSpecification) {
            return $jobCard->productionSpecification;
        }

        $direct = ProductionSpecification::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        if ($direct) {
            return $direct;
        }

        if (! $jobCard->sales_order_id) {
            return null;
        }

        return ProductionSpecification::query()
            ->where('sales_order_id', $jobCard->sales_order_id)
            ->orderBy('sales_order_item_id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForSalesOrderItem(SalesOrderItem $item, array $data, User $user): ProductionSpecification
    {
        $order = $item->salesOrder ?? $item->salesOrder()->firstOrFail();

        if ($this->findForSalesOrderItem($item)) {
            throw ValidationException::withMessages([
                'sales_order_item_id' => [__('A production specification already exists for this line item.')],
            ]);
        }

        $spec = ProductionSpecification::query()->create([
            ...$this->normalizePayload($data),
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'sales_order_item_id' => $item->id,
            'quotation_id' => $order->quotation_id,
            'print_product_template_id' => Arr::get($data, 'print_product_template_id'),
            'product_description' => Arr::get($data, 'product_description', $item->description ?? $item->item_name),
            'quantity' => Arr::get($data, 'quantity', $item->quantity),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return $this->refreshSnapshot($spec->fresh([
            'paperInventoryItem',
            'materialInventoryItem',
            'inkProfile',
            'customer',
            'salesOrderItem',
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ProductionSpecification $spec, array $data, User $user): ProductionSpecification
    {
        $spec->update([
            ...$this->normalizePayload($data),
            'updated_by' => $user->id,
        ]);

        return $this->refreshSnapshot($spec->fresh([
            'paperInventoryItem',
            'materialInventoryItem',
            'inkProfile',
            'customer',
            'salesOrderItem',
        ]));
    }

    public function linkToJobCard(ProductionSpecification $spec, ProductionJobCard $jobCard): ProductionSpecification
    {
        $spec->update([
            'production_job_card_id' => $jobCard->id,
            'sales_order_id' => $spec->sales_order_id ?? $jobCard->sales_order_id,
            'customer_id' => $spec->customer_id ?? $jobCard->customer_id,
            'quotation_id' => $spec->quotation_id ?? $jobCard->quotation_id,
        ]);

        return $this->refreshSnapshot($spec->fresh());
    }

    public function refreshSnapshot(ProductionSpecification $spec): ProductionSpecification
    {
        $presented = $this->presenter->present($spec);
        $spec->update(['snapshot_payload' => $presented]);
        $spec->refresh();

        return $spec;
    }

    /**
     * @return array<string, mixed>
     */
    public function present(?ProductionSpecification $spec): array
    {
        if (! $spec) {
            return $this->presenter->emptyState();
        }

        return $this->presenter->present($spec);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentSummary(?ProductionSpecification $spec): array
    {
        if (! $spec) {
            return ['has_specification' => false];
        }

        return $this->presenter->presentSummary($spec);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data): array
    {
        $keys = array_keys($this->validationRules());
        $payload = Arr::only($data, $keys);

        foreach (['lamination', 'foiling', 'spot_uv', 'embossing', 'debossing', 'die_cutting', 'creasing', 'perforation', 'numbering_required', 'eyelets'] as $boolKey) {
            if (array_key_exists($boolKey, $payload)) {
                $payload[$boolKey] = filter_var($payload[$boolKey], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (! array_key_exists('approval_status', $payload) || $payload['approval_status'] === null) {
            unset($payload['approval_status']);
        }

        return $payload;
    }
}
