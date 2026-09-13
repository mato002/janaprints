<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Support\NewestFirst;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReceivablesInvoiceDesk
{
    /**
     * @return array{
     *     customer: ?Customer,
     *     customers: Collection<int, Customer>,
     *     unbilledOrders: Collection<int, SalesOrder>,
     *     invoices: LengthAwarePaginator,
     *     customerQuery: string
     * }
     */
    public function build(Request $request, Builder $invoiceQuery, Builder $orderQuery): array
    {
        $customer = $this->resolveCustomer($request);
        $customerQuery = trim($request->string('customer')->toString());

        $orders = NewestFirst::apply(
            $orderQuery
                ->with(['customer', 'items'])
                ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
                ->when($customer, fn (Builder $query) => $query->where('customer_id', $customer->id))
        )
            ->limit(200)
            ->get()
            ->filter(fn (SalesOrder $order) => $order->remainingInvoiceTotal() > 0)
            ->values();

        $invoices = NewestFirst::apply(
            $invoiceQuery
                ->with(['customer', 'salesOrder', 'salesOrders'])
                ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
                ->when($customer, fn (Builder $query) => $query->where('customer_id', $customer->id))
        )->paginate(20)->withQueryString();

        $customerIds = $orders->pluck('customer_id')
            ->merge($invoices->getCollection()->pluck('customer_id'))
            ->filter()
            ->unique()
            ->values();

        $customers = Customer::query()
            ->whereIn('id', $customerIds)
            ->orderBy('company_name')
            ->get(['id', 'public_id', 'company_name']);

        return [
            'customer' => $customer,
            'customers' => $customers,
            'unbilledOrders' => $orders,
            'invoices' => $invoices,
            'customerQuery' => $customerQuery,
        ];
    }

    protected function resolveCustomer(Request $request): ?Customer
    {
        if ($request->filled('customer_id')) {
            return Customer::query()->whereKey($request->integer('customer_id'))->first();
        }

        $term = trim($request->string('customer')->toString());

        if ($term === '') {
            return null;
        }

        return Customer::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('company_name', 'like', '%'.$term.'%')
                    ->orWhere('customer_code', 'like', '%'.$term.'%');
            })
            ->orderBy('company_name')
            ->first();
    }
}
