<?php

namespace App\Support\Sales;

use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Sales\SalesOrder;
use App\Support\Crm\CustomerPrintSpecificationService;
use Illuminate\Http\Request;

class SalesDeskPageBuilder
{
    public function __construct(
        protected SalesDeskService $desk,
        protected CustomerPrintSpecificationService $printSpecifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();

        $step = max(1, min(4, (int) $request->query('step', 1)));
        $customer = $this->resolveCustomer($request);
        $specification = $this->resolveSpecification($request, $customer);
        $order = $this->resolveOrder($request, $customer);

        if ($order) {
            $step = 4;
        } elseif ($specification && $customer) {
            $step = max($step, 3);
        } elseif ($customer) {
            $step = max($step, 2);
        }

        $specs = $customer
            ? $this->printSpecifications->selectableForOrderContext($customer)
            : [];

        $notice = $this->specificationNotice($request, $customer, $specification);

        return [
            'operatorMode' => SalesOperatorMode::enabledFor($user),
            'step' => $step,
            'customer' => $customer,
            'specification' => $specification,
            'order' => $order,
            'orderPresentation' => $order ? $this->desk->presentOrder($order) : null,
            'printSpecifications' => $specs,
            'specificationNotice' => $notice['message'],
            'searchUrl' => route('admin.sales.desk.customers.search'),
            'fullCommercialDeskUrl' => route('admin.workspaces.commercial', ['desk' => 1]),
        ];
    }

    protected function resolveCustomer(Request $request): ?Customer
    {
        $key = $request->query('customer');
        if ($key === null || $key === '') {
            return null;
        }

        // MySQL coerces '9Yy…' → 9 when compared to integer id. Never whereKey() a public hash.
        $query = Customer::query()->forTenant();

        if (ctype_digit((string) $key)) {
            $customer = $query->where('id', (int) $key)->first();
        } else {
            $customer = $query->where('public_id', (string) $key)->first();
        }

        if ($customer && $request->user()?->can('view', $customer)) {
            return $customer;
        }

        return null;
    }

    protected function resolveSpecification(Request $request, ?Customer $customer): ?CustomerPrintSpecification
    {
        if (! $customer) {
            return null;
        }

        $id = $request->integer('specification');
        if (! $id) {
            return null;
        }

        // Resolve by explicit id tied to this customer (avoid branch-scoped misses / cross-customer leftovers in the URL).
        return CustomerPrintSpecification::query()
            ->where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->with(['inventoryItem:id,item_name,sku,stock_role', 'activeArtworkVersion'])
            ->first();
    }

    /**
     * @return array{mismatch: bool, message: ?string}
     */
    protected function specificationNotice(Request $request, ?Customer $customer, ?CustomerPrintSpecification $specification): array
    {
        $id = $request->integer('specification');
        if (! $id || ! $customer || $specification) {
            return ['mismatch' => false, 'message' => null];
        }

        $foreign = CustomerPrintSpecification::query()
            ->where('company_id', $customer->company_id)
            ->where('id', $id)
            ->first();

        if ($foreign && (int) $foreign->customer_id !== (int) $customer->id) {
            return [
                'mismatch' => true,
                'message' => __('That print specification belongs to another customer. Create or select one for :customer.', [
                    'customer' => $customer->name,
                ]),
            ];
        }

        return [
            'mismatch' => true,
            'message' => __('Print specification not found for this customer. Create one or select an active specification.'),
        ];
    }

    protected function resolveOrder(Request $request, ?Customer $customer): ?SalesOrder
    {
        $key = $request->query('order');
        if ($key === null || $key === '') {
            return null;
        }

        $query = SalesOrder::query()->forTenant();

        if (ctype_digit((string) $key)) {
            $order = $query->where('id', (int) $key)->first();
        } else {
            $order = $query->where('public_id', (string) $key)->first();
        }

        if (! $order || ! $request->user()?->can('view', $order)) {
            return null;
        }

        if ($customer && (int) $order->customer_id !== (int) $customer->id) {
            return null;
        }

        return $order->loadMissing(['jobCard', 'customer']);
    }
}
