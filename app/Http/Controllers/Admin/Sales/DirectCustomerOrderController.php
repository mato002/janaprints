<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\CustomerOrderContextService;
use App\Support\Sales\DirectCustomerSalesOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DirectCustomerOrderController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected CustomerOrderContextService $context,
        protected DirectCustomerSalesOrderService $orders,
    ) {}

    public function context(Request $request, Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $payload = $request->query('scope') === 'direct-order'
            ? $this->context->buildForDirectOrder($customer)
            : $this->context->build($customer);

        return response()->json($payload);
    }

    public function orderSpecification(Customer $customer, SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('view', $customer);

        abort_unless((int) $salesOrder->customer_id === (int) $customer->id, 404);

        return response()->json($this->context->orderSpecification($salesOrder));
    }

    public function repeat(Request $request, Customer $customer, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('view', $customer);
        $this->authorize('create', SalesOrder::class);

        abort_unless((int) $salesOrder->customer_id === (int) $customer->id, 404);

        $validated = $request->validate([
            'customer_print_specification_id' => ['nullable', 'exists:customer_print_specifications,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
            'required_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($validated['customer_print_specification_id'])) {
            $spec = \App\Models\Crm\CustomerPrintSpecification::query()
                ->forTenant()
                ->where('customer_id', $customer->id)
                ->findOrFail($validated['customer_print_specification_id']);

            $order = $this->orders->repeatFromPrintSpecification($spec, (int) $request->user()->id, [
                ...$validated,
                'repeat_source_sales_order_id' => $salesOrder->id,
                'unit_price' => (float) ($salesOrder->items->first()?->unit_price ?? 0),
            ]);
        } else {
            $order = $this->orders->repeatFrom($salesOrder, (int) $request->user()->id, $validated);
        }

        return redirect()
            ->route('admin.sales-orders.show', $order)
            ->with('status', __('Repeat order created.'));
    }
}
