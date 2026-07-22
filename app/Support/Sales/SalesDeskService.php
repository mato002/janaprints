<?php

namespace App\Support\Sales;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
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
    ) {}

    /**
     * @return list<array{id: int, label: string, code: string|null, phone: string|null, email: string|null}>
     */
    public function searchCustomers(string $query, int $limit = 12): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return Customer::query()
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
        $salesOrder->loadMissing(['jobCard:id,sales_order_id,job_card_number', 'customer:id,company_name,contact_person,customer_code']);

        $readiness = $this->releaseReadiness->assess($salesOrder);

        return [
            'id' => $salesOrder->id,
            'order_number' => $salesOrder->order_number,
            'status' => $salesOrder->status->value,
            'status_label' => str_replace('_', ' ', ucfirst($salesOrder->status->value)),
            'customer' => $salesOrder->customer
                ? $this->presentCustomer($salesOrder->customer)
                : null,
            'can_release' => $this->workflow->canRelease($salesOrder),
            'readiness' => $readiness,
            'job_card_id' => $salesOrder->jobCard?->id,
            'job_card_number' => $salesOrder->jobCard?->job_card_number,
            'show_url' => route('admin.sales-orders.show', [$salesOrder, 'from' => 'sales-desk']),
            'job_url' => $salesOrder->jobCard
                ? route('admin.production.job-cards.show', [$salesOrder->jobCard, 'from' => 'sales-desk'])
                : null,
        ];
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
            'fullCommercialDesk' => route('admin.workspaces.commercial', ['desk' => 1]),
        ];
    }
}
