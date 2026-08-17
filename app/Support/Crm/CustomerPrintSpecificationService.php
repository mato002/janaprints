<?php

namespace App\Support\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\InventoryStockRole;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Inventory\InventoryItem;
use App\Support\Production\PrintSpecificationJobFields;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPrintSpecificationService
{
    public function __construct(
        protected CustomerPrintSpecificationLifecycleService $lifecycle,
        protected CustomerPrintSpecificationUsageService $usage,
    ) {}
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Customer $customer, array $data, int $userId): CustomerPrintSpecification
    {
        return DB::transaction(function () use ($customer, $data, $userId) {
            $data = app(PrintSpecificationJobFields::class)->enrichSpecificationData($data);
            $data = $this->resolveProductFields($data, $customer);

            $spec = CustomerPrintSpecification::query()->create([
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'inventory_item_id' => $data['inventory_item_id'] ?? null,
                'product_name' => $data['product_name'] ?? null,
                'specification_code' => $this->nextSpecificationCode($customer->company_id),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? CustomerPrintSpecificationStatus::Draft,
                'production_notes' => $data['production_notes'] ?? null,
                'commercial_notes' => $data['commercial_notes'] ?? null,
                'customer_instructions' => $data['customer_instructions'] ?? null,
                'default_quantity' => $data['default_quantity'] ?? null,
                'default_unit_price' => $data['default_unit_price'] ?? null,
                'default_billing_type' => $data['default_billing_type'] ?? null,
                'default_fulfilment_method' => $data['default_fulfilment_method'] ?? null,
                'production_destination' => $data['production_destination'] ?? null,
                'job_sheet_payload' => $data['job_sheet_payload'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if (($data['status'] ?? CustomerPrintSpecificationStatus::Draft) === CustomerPrintSpecificationStatus::Active
                || ($data['status'] ?? null) === CustomerPrintSpecificationStatus::Active->value) {
                $this->assertCanActivate($spec);
            }

            return $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CustomerPrintSpecification $spec, array $data, int $userId): CustomerPrintSpecification
    {
        return DB::transaction(function () use ($spec, $data, $userId) {
            $data = app(PrintSpecificationJobFields::class)->enrichSpecificationData($data);
            $data = $this->resolveProductFields($data, $spec->customer ?? $spec->customer()->firstOrFail());

            $this->lifecycle->assertSafeUpdate($spec, $data);

            $nextStatus = isset($data['status'])
                ? ($data['status'] instanceof CustomerPrintSpecificationStatus
                    ? $data['status']
                    : CustomerPrintSpecificationStatus::from($data['status']))
                : $spec->status;

            if ($nextStatus !== $spec->status) {
                $spec = $this->lifecycle->transition($spec, $nextStatus, $userId);
            }

            if ($spec->status === CustomerPrintSpecificationStatus::Active) {
                $spec->fill([
                    'inventory_item_id' => $data['inventory_item_id'] ?? null,
                    'product_name' => $data['product_name'] ?? $spec->product_name,
                ]);
                $this->assertCanActivate($spec);
            }

            if ($spec->status->isReadOnly()) {
                return $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
            }

            $spec->update([
                ...collect($data)->only([
                    'inventory_item_id',
                    'product_name',
                    'name',
                    'description',
                    'production_notes',
                    'commercial_notes',
                    'customer_instructions',
                    'default_quantity',
                    'default_unit_price',
                    'default_billing_type',
                    'default_fulfilment_method',
                    'production_destination',
                    'job_sheet_payload',
                ])->all(),
                'updated_by' => $userId,
            ]);

            return $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
        });
    }

    /**
     * @param  array<string, mixed>|int  $filters
     */
    public function paginateForCustomer(Customer $customer, array|int $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (is_int($filters)) {
            $perPage = $filters;
            $filters = [];
        }

        $query = CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->with([
                'inventoryItem:id,item_name,sku,uses_serial_numbers,serial_prefix,serial_padding_length',
                'activeArtworkVersion:id,customer_print_specification_id,version_number,original_file_name,file_name,status',
                'customer:id',
            ]);

        $this->applySearchFilters($query, $filters, $customer);

        $paginator = $query
            ->latest('updated_at')
            ->paginate($perPage, ['*'], 'spec_page')
            ->withQueryString();

        $collection = $paginator->getCollection();
        $usageMetrics = $this->usage->batchUsageMetrics($collection);
        $serialSummaries = $this->batchSerialSummaries($collection->loadMissing('customer'));

        $collection->transform(function (CustomerPrintSpecification $spec) use ($usageMetrics, $serialSummaries) {
            $metrics = $usageMetrics[$spec->id] ?? [];
            $spec->setAttribute('orders_count', $metrics['orders_count'] ?? 0);
            $spec->setAttribute('total_revenue', $metrics['total_revenue'] ?? 0);
            $spec->setAttribute('last_used_at', $metrics['last_ordered_at'] ?? null);
            $spec->setAttribute('usage_metrics', $metrics);
            $spec->setAttribute('serial_summary', $serialSummaries[$spec->id] ?? $this->emptySerialSummary());

            return $spec;
        });

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function searchForCustomer(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginateForCustomer($customer, $filters, $perPage);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<CustomerPrintSpecification>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applySearchFilters($query, array $filters, Customer $customer): void
    {
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('specification_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($productId = $filters['product_id'] ?? null) {
            $query->where('inventory_item_id', $productId);
        }

        if ($serialPrefix = trim((string) ($filters['serial_prefix'] ?? ''))) {
            $query->whereHas('inventoryItem', fn ($q) => $q->where('serial_prefix', 'like', "%{$serialPrefix}%"));
        }

        if ($artworkVersion = $filters['artwork_version'] ?? null) {
            $query->whereHas('activeArtworkVersion', fn ($q) => $q->where('version_number', $artworkVersion));
        }
    }

    public function nextSpecificationCode(int $companyId): string
    {
        $sequence = (int) CustomerPrintSpecification::query()
            ->where('company_id', $companyId)
            ->count() + 1;

        do {
            $code = 'CPS-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            $sequence++;
        } while (CustomerPrintSpecification::query()
            ->where('company_id', $companyId)
            ->where('specification_code', $code)
            ->exists());

        return $code;
    }

    public function assertCanActivate(CustomerPrintSpecification $spec): void
    {
        if (! $spec->hasProduct()) {
            throw ValidationException::withMessages([
                'product_name' => __('Enter the finished product — for example a book, brochure, or flyer.'),
            ]);
        }
    }

    /**
     * @return list<array{value: int, label: string, name: string}>
     */
    public function finishedProductOptions(Customer $customer): array
    {
        return InventoryItem::query()
            ->forTenant()
            ->where('company_id', $customer->company_id)
            ->where('branch_id', $customer->branch_id)
            ->where('is_active', true)
            ->where('stock_role', InventoryStockRole::FinishedGood)
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'sku'])
            ->map(fn (InventoryItem $item) => [
                'value' => $item->id,
                'name' => $item->item_name,
                'label' => trim($item->item_name.($item->sku ? ' ('.$item->sku.')' : '')),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function resolveProductFields(array $data, Customer $customer): array
    {
        $typed = trim((string) ($data['product_name'] ?? ''));
        $itemId = filled($data['inventory_item_id'] ?? null) ? (int) $data['inventory_item_id'] : null;
        $item = null;

        if ($itemId) {
            $item = InventoryItem::query()
                ->forTenant()
                ->where('company_id', $customer->company_id)
                ->where('branch_id', $customer->branch_id)
                ->whereKey($itemId)
                ->first();

            if (! $item) {
                throw ValidationException::withMessages([
                    'inventory_item_id' => __('The selected product is invalid for this branch.'),
                ]);
            }

            if ($item->stock_role !== InventoryStockRole::FinishedGood) {
                $item = null;
            }
        } elseif ($typed !== '') {
            $item = InventoryItem::query()
                ->forTenant()
                ->where('company_id', $customer->company_id)
                ->where('branch_id', $customer->branch_id)
                ->where('is_active', true)
                ->where('stock_role', InventoryStockRole::FinishedGood)
                ->whereRaw('LOWER(item_name) = ?', [mb_strtolower($typed)])
                ->first();
        }

        $data['inventory_item_id'] = $item?->id;
        $data['product_name'] = $typed !== '' ? $typed : ($item?->item_name);

        if (! filled($data['product_name'])) {
            throw ValidationException::withMessages([
                'product_name' => __('Enter the finished product — for example a book, brochure, or flyer.'),
            ]);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialSummary(CustomerPrintSpecification $spec): array
    {
        $spec->loadMissing(['inventoryItem', 'customer']);
        $summaries = $this->batchSerialSummaries(collect([$spec]));

        return $summaries[$spec->id] ?? $this->emptySerialSummary();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function batchSerialSummaries(Collection $specifications): array
    {
        if ($specifications->isEmpty()) {
            return [];
        }

        $governance = app(\App\Support\Production\SerialNumberGovernanceService::class);
        $summaries = [];

        $itemIds = $specifications->pluck('inventory_item_id')->filter()->unique()->values();
        $customerIds = $specifications->pluck('customer_id')->filter()->unique()->values();

        $counters = \App\Models\Production\SerialNumberCounter::query()
            ->whereIn('inventory_item_id', $itemIds)
            ->whereIn('customer_id', $customerIds)
            ->get()
            ->keyBy(fn ($counter) => $counter->customer_id.'_'.$counter->inventory_item_id);

        $allocations = \App\Models\Production\JobCardSerialAllocation::query()
            ->whereIn('inventory_item_id', $itemIds)
            ->with('jobCard:id,customer_id')
            ->whereHas('jobCard', fn ($q) => $q->whereIn('customer_id', $customerIds))
            ->orderByDesc('id')
            ->get(['id', 'production_job_card_id', 'inventory_item_id', 'serial_prefix', 'serial_start', 'serial_end', 'produced_end'])
            ->groupBy(fn ($allocation) => $allocation->jobCard?->customer_id.'_'.$allocation->inventory_item_id);

        foreach ($specifications as $spec) {
            $item = $spec->inventoryItem;
            $customer = $spec->customer;

            if (! $item || ! $customer) {
                $summaries[$spec->id] = $this->emptySerialSummary();

                continue;
            }

            $profile = $governance->resolveProfile($item, $customer);
            $counterKey = $customer->id.'_'.$item->id;
            $counter = $counters->get($counterKey);
            $lastAllocation = ($allocations->get($counterKey) ?? collect())->first();

            $summaries[$spec->id] = [
                'uses_serial_numbers' => (bool) $item->uses_serial_numbers,
                'product_prefix' => $item->serial_prefix,
                'product_padding' => (int) ($item->serial_padding_length ?: 6),
                'customer_prefix' => $profile['prefix'],
                'customer_padding' => $profile['padding_length'],
                'resolved_prefix' => $profile['prefix'],
                'resolved_padding' => $profile['padding_length'],
                'next_number' => $counter ? ((int) $counter->last_serial_number + 1) : 1,
                'last_counter' => $counter?->last_serial_number,
                'last_allocation' => $lastAllocation ? [
                    'prefix' => $lastAllocation->serial_prefix,
                    'start' => $lastAllocation->serial_start,
                    'end' => $lastAllocation->serial_end,
                    'produced_end' => $lastAllocation->produced_end,
                ] : null,
            ];
        }

        return $summaries;
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptySerialSummary(): array
    {
        return [
            'uses_serial_numbers' => false,
            'product_prefix' => null,
            'product_padding' => null,
            'customer_prefix' => null,
            'customer_padding' => null,
            'resolved_prefix' => null,
            'resolved_padding' => null,
            'next_number' => null,
            'last_counter' => null,
            'last_allocation' => null,
        ];
    }

    /**
     * @param  Collection<int, CustomerPrintSpecification>  $specifications
     * @return array<int, mixed>
     */
    protected function batchLastUsedDates(Customer $customer, Collection $specifications): array
    {
        if ($specifications->isEmpty()) {
            return [];
        }

        $specIds = $specifications->pluck('id')->all();
        $itemIds = $specifications->pluck('inventory_item_id')->filter()->unique()->all();

        $bySpecId = \App\Models\Sales\SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->whereIn('customer_print_specification_id', $specIds)
            ->selectRaw('customer_print_specification_id, MAX(order_date) as last_used')
            ->groupBy('customer_print_specification_id')
            ->pluck('last_used', 'customer_print_specification_id')
            ->all();

        $byItemId = \App\Models\Sales\SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->whereIn('inventory_item_id', $itemIds)
            ->selectRaw('inventory_item_id, MAX(order_date) as last_used')
            ->groupBy('inventory_item_id')
            ->pluck('last_used', 'inventory_item_id')
            ->all();

        $dates = [];

        foreach ($specifications as $spec) {
            $dates[$spec->id] = $bySpecId[$spec->id]
                ?? ($spec->inventory_item_id ? ($byItemId[$spec->inventory_item_id] ?? null) : null);
        }

        return $dates;
    }

    /**
     * @return array{on_record: int, selectable: int, draft: int, missing_product: int}
     */
    public function orderSelectionSummary(Customer $customer): array
    {
        $onRecord = CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->whereIn('status', [
                CustomerPrintSpecificationStatus::Draft,
                CustomerPrintSpecificationStatus::Active,
            ])
            ->get(['id', 'status', 'inventory_item_id', 'product_name']);

        return [
            'on_record' => $onRecord->count(),
            'selectable' => count($this->selectableForOrderContext($customer)),
            'draft' => $onRecord->where('status', CustomerPrintSpecificationStatus::Draft)->count(),
            'missing_product' => $onRecord->filter(fn ($spec) => $spec->inventory_item_id === null && ! filled($spec->product_name))->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableForOrderContext(Customer $customer): array
    {
        $specs = CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerPrintSpecificationStatus::Active)
            ->with([
                'inventoryItem:id,item_name,sku,uses_serial_numbers,serial_prefix,serial_padding_length,stock_role',
                'activeArtworkVersion:id,customer_print_specification_id,version_number,original_file_name,file_name',
                'customer:id',
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (CustomerPrintSpecification $spec) => $spec->hasProduct())
            ->values();

        if ($specs->isEmpty()) {
            return [];
        }

        $serialSummaries = $this->batchSerialSummaries($specs);
        $lastUsedDates = $this->batchLastUsedDates($customer, $specs);

        return $specs
            ->map(fn (CustomerPrintSpecification $spec) => $this->formatForOrderContext(
                $spec,
                $serialSummaries[$spec->id] ?? null,
                $lastUsedDates[$spec->id] ?? null,
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatForOrderContext(
        CustomerPrintSpecification $spec,
        ?array $serialSummary = null,
        mixed $lastUsedAt = null,
    ): array {
        $activeArtwork = $spec->activeArtworkVersion;
        $serial = $serialSummary ?? $this->serialSummary($spec);
        $item = $spec->inventoryItem;

        if ($lastUsedAt === null) {
            $lastUsedAt = \App\Models\Sales\SalesOrder::query()
                ->where('customer_id', $spec->customer_id)
                ->where('customer_print_specification_id', $spec->id)
                ->max('order_date');

            if (! $lastUsedAt && $spec->inventory_item_id) {
                $lastUsedAt = \App\Models\Sales\SalesOrder::query()
                    ->where('customer_id', $spec->customer_id)
                    ->where('inventory_item_id', $spec->inventory_item_id)
                    ->max('order_date');
            }
        }

        return [
            'id' => $spec->id,
            'specification_code' => $spec->specification_code,
            'name' => $spec->name,
            'status' => $spec->status->value,
            'inventory_item_id' => $spec->inventory_item_id,
            'product_name' => $spec->productLabel(),
            'product_sku' => $item?->sku,
            'current_artwork_version_id' => $activeArtwork?->id,
            'current_artwork_version' => $activeArtwork?->version_number,
            'current_artwork_label' => $activeArtwork?->versionLabel(),
            'has_active_artwork' => $activeArtwork !== null,
            'artwork_required' => $item?->stock_role === \App\Enums\InventoryStockRole::FinishedGood,
            'serial_summary' => $serial,
            'default_quantity' => $spec->default_quantity !== null ? (float) $spec->default_quantity : null,
            'default_unit_price' => $spec->default_unit_price !== null ? (float) $spec->default_unit_price : null,
            'default_billing_type' => $spec->default_billing_type?->value,
            'default_fulfilment_method' => $spec->default_fulfilment_method?->value,
            'production_notes' => $spec->production_notes,
            'commercial_notes' => $spec->commercial_notes,
            'customer_instructions' => $spec->customer_instructions,
            'last_used_at' => $lastUsedAt,
            'can_edit' => ! $spec->isReadOnly(),
            'edit_url' => $spec->isReadOnly()
                ? null
                : route('admin.crm.print-specifications.quick-edit', $spec),
            ...app(PrintSpecificationJobFields::class)->orderContextFields($spec),
        ];
    }
}
