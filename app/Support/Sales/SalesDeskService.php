<?php

namespace App\Support\Sales;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Services\Production\ProductionReleaseReadinessService;
use App\Support\Crm\CustomerPrintSpecificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SalesDeskService
{
    public function __construct(
        protected CustomerOrderContextService $orderContext,
        protected CustomerPrintSpecificationService $printSpecifications,
        protected ProductionReleaseReadinessService $releaseReadiness,
        protected SalesOrderWorkflowService $workflow,
        protected SalesDeskActionPresenter $actions,
        protected SalesDeskCustomerContextService $customerContext,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function searchDesk(string $query, int $limit = 20): array
    {
        $query = trim($query);

        if ($query === '') {
            return collect($this->searchCustomers('', min($limit, 15)))
                ->map(fn (array $row) => [
                    'kind' => 'customer',
                    ...$row,
                    'meta' => collect([$row['code'] ?? null, $row['phone'] ?? null, $row['email'] ?? null])->filter()->implode(' · '),
                    'url' => route('admin.sales.desk', [
                        'customer' => $row['key'] ?? $row['id'],
                        'step' => 2,
                    ]),
                    'modal' => false,
                ])
                ->values()
                ->all();
        }

        $results = collect();

        Customer::query()
            ->forTenant()
            ->where('status', CustomerStatus::Active)
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('company_name', 'like', "%{$query}%")
                    ->orWhere('contact_person', 'like', "%{$query}%")
                    ->orWhere('customer_code', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('company_name')
            ->limit(8)
            ->get(['id', 'company_name', 'contact_person', 'customer_code', 'phone', 'email', 'public_id'])
            ->each(function (Customer $customer) use ($results) {
                $presented = $this->presentCustomer($customer);
                $results->push([
                    'kind' => 'customer',
                    ...$presented,
                    'meta' => collect([$presented['code'], $presented['phone'], $presented['email']])->filter()->implode(' · '),
                    'url' => route('admin.sales.desk', [
                        'customer' => $presented['key'],
                        'step' => 2,
                    ]),
                    'modal' => false,
                ]);
            });

        Quotation::query()
            ->forTenant()
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('quotation_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($customer) use ($query) {
                        $customer
                            ->where('company_name', 'like', "%{$query}%")
                            ->orWhere('contact_person', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->with('customer:id,company_name,contact_person,public_id')
            ->latest('quotation_date')
            ->limit(5)
            ->get()
            ->each(function (Quotation $quote) use ($results) {
                $results->push([
                    'kind' => 'quotation',
                    'id' => $quote->id,
                    'key' => $quote->getRouteKey(),
                    'label' => $quote->quotation_number,
                    'meta' => $quote->customer?->name,
                    'url' => route('admin.quotations.show', [$quote, 'from' => 'sales-desk']),
                    'modal' => true,
                ]);
            });

        SalesOrder::query()
            ->forTenant()
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('order_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($customer) use ($query) {
                        $customer
                            ->where('company_name', 'like', "%{$query}%")
                            ->orWhere('contact_person', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->with('customer:id,company_name,contact_person,public_id')
            ->latest('order_date')
            ->limit(5)
            ->get()
            ->each(function (SalesOrder $order) use ($results) {
                $customerKey = $order->customer?->getRouteKey();

                $results->push([
                    'kind' => 'order',
                    'id' => $order->id,
                    'key' => $order->getRouteKey(),
                    'label' => $order->order_number,
                    'meta' => $order->customer?->name,
                    'url' => $customerKey
                        ? route('admin.sales.desk', [
                            'customer' => $customerKey,
                            'order' => $order->getRouteKey(),
                            'step' => 4,
                        ])
                        : route('admin.sales-orders.show', [$order, 'from' => 'sales-desk']),
                    'modal' => ! $customerKey,
                ]);
            });

        ProductionJobCard::query()
            ->forTenant()
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('job_card_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($customer) use ($query) {
                        $customer
                            ->where('company_name', 'like', "%{$query}%")
                            ->orWhere('contact_person', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->with('customer:id,company_name,contact_person,public_id')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function (ProductionJobCard $job) use ($results) {
                $results->push([
                    'kind' => 'job',
                    'id' => $job->id,
                    'key' => $job->getRouteKey(),
                    'label' => $job->job_card_number,
                    'meta' => $job->customer?->name,
                    'url' => route('admin.production.job-cards.show', [$job, 'from' => 'sales-desk']),
                    'modal' => true,
                ]);
            });

        return $results->take($limit)->values()->all();
    }

    /**
     * @return list<array{id: int, label: string, code: string|null, phone: string|null, email: string|null}>
     */
    public function searchCustomers(string $query, int $limit = 20): array
    {
        $query = trim($query);

        $builder = Customer::query()
            ->forTenant()
            ->where('status', CustomerStatus::Active);

        if ($query !== '') {
            $builder->where(function ($builder) use ($query) {
                $builder
                    ->where('company_name', 'like', "%{$query}%")
                    ->orWhere('contact_person', 'like', "%{$query}%")
                    ->orWhere('customer_code', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }

        return $builder
            ->orderBy('company_name')
            ->limit($limit)
            ->get(['id', 'company_name', 'contact_person', 'customer_code', 'phone', 'email'])
            ->map(fn (Customer $customer) => $this->presentCustomer($customer))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, label: string, code: string|null, phone: string|null, email: string|null}
     */
    public function presentCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'key' => $customer->getRouteKey(),
            'label' => $customer->name,
            'code' => $customer->customer_code,
            'phone' => $customer->phone,
            'email' => $customer->email,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerContext(Customer $customer): array
    {
        return [
            'customer' => $this->presentCustomer($customer),
            ...$this->orderContext->buildForDirectOrder($customer),
        ];
    }

    /**
     * @return list<array{id: int, label: string, sku: string|null}>
     */
    public function searchProducts(string $query, int $limit = 15): array
    {
        $query = trim($query);

        $builder = InventoryItem::query()
            ->forTenant()
            ->where('is_active', true);

        if ($query !== '') {
            $builder->where(function ($inner) use ($query) {
                $inner
                    ->where('item_name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            });
        }

        return $builder
            ->orderBy('item_name')
            ->limit($limit)
            ->get(['id', 'item_name', 'sku'])
            ->map(fn (InventoryItem $item) => [
                'id' => $item->id,
                'label' => $item->item_name,
                'sku' => $item->sku,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentOrder(SalesOrder $salesOrder): array
    {
        return $this->actions->presentOrder($salesOrder);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function presentCustomerContext(?Customer $customer, ?\App\Models\Crm\CustomerPrintSpecification $specification = null): ?array
    {
        return $this->customerContext->present($customer, $specification);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fastActions(?Customer $customer, ?\App\Models\Crm\CustomerPrintSpecification $specification = null, ?SalesOrder $order = null): array
    {
        return $this->actions->fastActions($customer, $specification, $order);
    }

    /**
     * @return array<string, mixed>
     */
    public function deskUrls(?Customer $customer, ?\App\Models\Crm\CustomerPrintSpecification $specification = null): array
    {
        return $this->actions->deskUrls($customer, $specification);
    }

    /**
     * @return array{customer_types: list<array{value: string, label: string}>}
     */
    public function formMeta(): array
    {
        return [
            'customer_types' => Collection::make(CustomerType::cases())
                ->map(fn (CustomerType $type) => [
                    'value' => $type->value,
                    'label' => method_exists($type, 'label') ? $type->label() : ucfirst($type->value),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function routes(): array
    {
        return [
            'searchCustomers' => route('admin.sales.desk.customers.search'),
            'storeCustomer' => route('admin.sales.desk.customers.store'),
            'customerContext' => url('admin/sales/desk/customers'),
            'searchProducts' => route('admin.sales.desk.products.search'),
            'storeSpecification' => url('admin/sales/desk/customers'),
            'storeOrder' => route('admin.sales.desk.orders.store'),
            'orderReadiness' => url('admin/sales/desk/orders'),
            'releaseOrder' => url('admin/sales/desk/orders'),
            'fullCommercialDesk' => route('admin.workspaces.commercial.section', [
                'section' => 'sales',
                'tab' => 'quotations',
                'desk' => 1,
            ]),
        ];
    }
}
