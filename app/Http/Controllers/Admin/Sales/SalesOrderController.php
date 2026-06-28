<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesSalesOrderItems;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\FormSettingsService;
use App\Support\QuotationConversionService;
use App\Support\Sales\DirectCustomerSalesOrderService;
use App\Support\Sales\SalesOrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    use HandlesModalFormResponses, ManagesSalesOrderItems, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected DirectCustomerSalesOrderService $directOrders,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = $this->scopeToTenant(
            SalesOrder::query()->with(['customer', 'branch', 'quotation', 'creator'])
        )->latest('order_date')->paginate(15);

        return view('admin.sales.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SalesOrder::class);

        return view('admin.sales.orders.create', [
            ...$this->formMeta(),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
            'catalogueItems' => InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sku']),
            'selectedCustomerId' => $request->integer('customer_id') ?: null,
            'defaultTab' => $request->query('tab', $request->filled('customer_id') ? 'direct' : 'quotation'),
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', SalesOrder::class);

        if ($request->input('entry_mode') === 'direct') {
            return $this->storeDirectOrder($request);
        }

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $validated = $this->formSettings->validateRequest($request, 'sales_order', [
            'quotation_id' => ['exists:quotations,id'],
        ], $companyId, $branchId);

        $quotation = Quotation::query()->forTenant()->findOrFail($validated['quotation_id']);
        $this->authorize('view', $quotation);

        $salesOrder = QuotationConversionService::convert($quotation, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Sales order created from quotation.'),
            redirect()->route('admin.sales-orders.show', $salesOrder),
        );
    }

    protected function storeDirectOrder(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'repeat_source_sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'inventory_item_id' => ['required_without:repeat_source_sales_order_id', 'nullable', 'exists:inventory_items,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'required_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'uses_existing_artwork' => ['boolean'],
            'customer_artwork_id' => ['nullable', 'exists:customer_artworks,id'],
        ]);

        $validated['uses_existing_artwork'] = $request->boolean('uses_existing_artwork');

        if (! $validated['uses_existing_artwork']) {
            $validated['customer_artwork_id'] = null;
        }

        $customer = Customer::query()->forTenant()->findOrFail($validated['customer_id']);
        $this->authorize('view', $customer);

        if (! empty($validated['repeat_source_sales_order_id'])) {
            $source = SalesOrder::query()->forTenant()->findOrFail($validated['repeat_source_sales_order_id']);
            abort_unless((int) $source->customer_id === (int) $customer->id, 422);

            $salesOrder = $this->directOrders->repeatFrom($source, (int) $request->user()->id, [
                'quantity' => $validated['quantity'] ?? null,
                'required_date' => $validated['required_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } else {
            $salesOrder = $this->directOrders->createNewRun($customer, $validated, (int) $request->user()->id);
        }

        return $this->modalOrRedirect(
            __('Direct sales order created.'),
            redirect()->route('admin.sales-orders.show', $salesOrder),
        );
    }

    public function show(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load([
            'customer', 'quotation', 'artworkRequest', 'branch', 'creator', 'jobCard',
            'items.productionSpecification.paperInventoryItem',
            'invoices', 'orderNotes.user', 'attachments.uploader', 'conversion.converter',
        ]);

        $financial = app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->snapshot($salesOrder);
        $profitability = app(\App\Support\Commercial\Intelligence\CommercialJobProfitabilityService::class)
            ->snapshotForSalesOrder($salesOrder->id);
        $workflow = app(SalesOrderWorkflowService::class)->present($salesOrder);
        $specificationService = app(\App\Support\Production\ProductionSpecificationService::class);
        $itemSpecifications = $salesOrder->items->mapWithKeys(function ($item) use ($specificationService) {
            $model = $item->productionSpecification;

            return [
                $item->id => [
                    'model' => $model,
                    'summary' => $model ? $specificationService->presentSummary($model) : ['has_specification' => false],
                ],
            ];
        })->all();

        return view('admin.sales.orders.show', compact(
            'salesOrder',
            'financial',
            'profitability',
            'workflow',
            'itemSpecifications',
        ));
    }

    public function edit(SalesOrder $salesOrder): View
    {
        $this->authorize('update', $salesOrder);

        $salesOrder->load('items');

        app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->syncDepositAmounts($salesOrder);

        $customerArtworks = $salesOrder->customer_id
            ? \App\Models\Crm\CustomerArtwork::query()
                ->where('customer_id', $salesOrder->customer_id)
                ->where('is_active_version', true)
                ->orderBy('artwork_name')
                ->get(['id', 'artwork_name', 'version_number'])
            : collect();

        return view('admin.sales.orders.edit', [
            'salesOrder' => $salesOrder,
            'customerArtworks' => $customerArtworks,
            'catalogueItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sku']),
            ...$this->formMeta(),
        ]);
    }

    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse|Response
    {
        $this->authorize('update', $salesOrder);

        $header = $request->validate([
            'order_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'fulfilment_method' => ['nullable', 'string', 'in:collection,delivery'],
            'billing_type' => ['nullable', 'string', 'in:deposit_50,advance_100,net_30'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string'],
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'uses_existing_artwork' => ['boolean'],
            'customer_artwork_id' => ['nullable', 'exists:customer_artworks,id'],
        ]);

        $header['uses_existing_artwork'] = $request->boolean('uses_existing_artwork');

        if ($header['uses_existing_artwork'] && ! empty($header['customer_artwork_id'])) {
            $header['artwork_confirmed_by'] = auth()->id();
            $header['artwork_confirmed_at'] = now();
        } elseif (! $header['uses_existing_artwork']) {
            $header['customer_artwork_id'] = null;
            $header['artwork_confirmed_by'] = null;
            $header['artwork_confirmed_at'] = null;
        }

        ['items' => $items, 'totals' => $totals] = $this->validatedItems($request);

        $salesOrder->update($header);
        $this->syncItems($salesOrder, $items, $totals);

        app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->syncDepositAmounts($salesOrder->fresh());

        return $this->modalOrRedirect(
            __('Sales order updated.'),
            redirect()->route('admin.sales-orders.show', $salesOrder),
        );
    }

    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('delete', $salesOrder);

        $salesOrder->delete();

        return redirect()
            ->route('admin.sales-orders.index')
            ->with('status', __('Sales order deleted.'));
    }

    public function confirm(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('confirm', $salesOrder);

        if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::Confirmed)) {
            return back()->withErrors([
                'workflow' => __('Only draft sales orders can be confirmed.'),
            ]);
        }

        $salesOrder->transitionTo(SalesOrderStatus::Confirmed);

        $released = app(SalesOrderWorkflowService::class)->tryReleaseToProduction(
            $salesOrder,
            (int) auth()->id(),
        );

        return back()->with(
            'status',
            $released
                ? __('Sales order confirmed and sent to production.')
                : __('Sales order confirmed.'),
        );
    }

    public function releaseToProduction(SalesOrder $salesOrder, SalesOrderWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('production', $salesOrder);

        try {
            $workflow->releaseToProduction($salesOrder, (int) auth()->id());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', __('Sales order sent to production.'));
    }

    public function close(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('close', $salesOrder);

        if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::Closed)) {
            return back()->withErrors([
                'workflow' => __('This sales order cannot be closed in its current status.'),
            ]);
        }

        try {
            app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->assertCanClose($salesOrder);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        $salesOrder->transitionTo(SalesOrderStatus::Closed);

        return back()->with('status', __('Sales order closed.'));
    }

    public function hold(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);

        if ($response = $this->workflowTransitionError(
            $salesOrder,
            SalesOrderStatus::OnHold,
            __('This sales order cannot be placed on hold in its current status.'),
        )) {
            return $response;
        }

        $salesOrder->transitionTo(SalesOrderStatus::OnHold);

        return back()->with('status', __('Sales order placed on hold.'));
    }

    public function resume(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);

        if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::Confirmed)) {
            return back()->withErrors([
                'workflow' => __('Only orders on hold can be resumed to confirmed.'),
            ]);
        }

        $salesOrder->transitionTo(SalesOrderStatus::Confirmed);

        return back()->with('status', __('Sales order resumed.'));
    }

    public function cancel(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);

        if ($response = $this->workflowTransitionError(
            $salesOrder,
            SalesOrderStatus::Cancelled,
            __('This sales order cannot be cancelled in its current status.'),
        )) {
            return $response;
        }

        $salesOrder->transitionTo(SalesOrderStatus::Cancelled);

        return back()->with('status', __('Sales order cancelled.'));
    }

    protected function workflowTransitionError(
        SalesOrder $salesOrder,
        SalesOrderStatus $target,
        string $message,
    ): ?RedirectResponse {
        if ($salesOrder->status->canTransitionTo($target)) {
            return null;
        }

        return back()->withErrors(['workflow' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds(request());

        $eligible = Quotation::query()
            ->forTenant()
            ->eligibleForSalesOrderConversion()
            ->with('customer')
            ->orderByDesc('quotation_date')
            ->get();

        return [
            'formFields' => $this->formSettings->resolvedFields('sales_order', $companyId, $branchId),
            'eligibleQuotations' => $eligible,
        ];
    }
}
