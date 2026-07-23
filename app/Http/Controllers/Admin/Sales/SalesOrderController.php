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
use App\Support\Sales\ReturnsToSalesDesk;
use App\Support\Production\ReturnsToProductionFloor;
use App\Support\Sales\SalesOrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    use HandlesModalFormResponses, ManagesSalesOrderItems, ResolvesCrmTenant, ReturnsToProductionFloor, ReturnsToSalesDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected DirectCustomerSalesOrderService $directOrders,
        protected SalesOrderWorkflowService $workflow,
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
            'selectedCustomerId' => $request->integer('customer_id') ?: null,
            'selectedSpecificationId' => $request->integer('print_specification_id') ?: null,
            'selectedQuotationId' => $request->integer('quotation_id') ?: null,
            'defaultTab' => $this->resolveDefaultTab($request),
            'billingTypes' => \App\Enums\SalesOrderBillingType::cases(),
            'fulfilmentMethods' => \App\Enums\FulfilmentMethod::cases(),
            'priorities' => \App\Enums\ProductionPriority::cases(),
            'canSendToProduction' => auth()->user()?->can('sales_orders.production') ?? false,
            'canCreateSpecification' => auth()->user()?->can('crm.customers.edit') ?? false,
        ]);
    }

    protected function resolveDefaultTab(Request $request): string
    {
        if ($request->query('tab') === 'direct' || $request->filled('print_specification_id')) {
            return 'direct';
        }

        if ($request->query('tab') === 'quotation' || $request->filled('quotation_id')) {
            return 'quotation';
        }

        if ($request->filled('customer_id')) {
            return 'direct';
        }

        return 'quotation';
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
            'customer_print_specification_id' => ['required', 'exists:customer_print_specifications,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'required_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'fulfilment_method' => ['nullable', 'string', 'in:collection,delivery'],
            'billing_type' => ['nullable', 'string', 'in:deposit_50,advance_100,net_30'],
            'repeat_source_sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'send_to_production' => ['sometimes', 'boolean'],
        ]);

        $customer = Customer::query()->forTenant()->findOrFail($validated['customer_id']);
        $this->authorize('view', $customer);

        $specification = \App\Models\Crm\CustomerPrintSpecification::query()
            ->forTenant()
            ->where('customer_id', $customer->id)
            ->findOrFail($validated['customer_print_specification_id']);

        if (! empty($validated['repeat_source_sales_order_id'])) {
            $source = SalesOrder::query()->forTenant()->findOrFail($validated['repeat_source_sales_order_id']);
            abort_unless((int) $source->customer_id === (int) $customer->id, 422);
            $validated['repeat_source_sales_order_id'] = $source->id;
        }

        $salesOrder = $this->directOrders->createFromPrintSpecification(
            $specification,
            $validated,
            (int) $request->user()->id,
        );

        $message = __('Direct sales order created.');
        $redirect = redirect()->route('admin.sales-orders.show', $salesOrder);

        if ($this->wantsSalesDeskReturn($request)) {
            $redirect = redirect()->route('admin.sales.desk', [
                'customer' => $customer->getRouteKey(),
                'order' => $salesOrder->getRouteKey(),
                'step' => 4,
            ]);
        }

        if ($request->boolean('send_to_production')) {
            $this->authorize('production', $salesOrder);

            try {
                $this->workflow->releaseToProduction($salesOrder, (int) $request->user()->id);
                $message = __('Direct sales order created and sent to production.');

                $jobCard = $salesOrder->fresh('jobCard')->jobCard;
                if ($this->wantsSalesDeskReturn($request)) {
                    $redirect = redirect()->route('admin.sales.desk', [
                        'customer' => $customer->getRouteKey(),
                        'order' => $salesOrder->fresh()->getRouteKey(),
                        'step' => 4,
                    ]);
                } elseif ($jobCard !== null) {
                    $redirect = redirect()->route('admin.production.job-cards.show', $jobCard);
                }
            } catch (\Illuminate\Validation\ValidationException $exception) {
                return $redirect
                    ->with('status', $message)
                    ->withErrors($exception->errors());
            }
        }

        return $this->modalOrRedirect($message, $redirect);
    }

    public function show(Request $request, SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load([
            'customer', 'quotation', 'artworkRequest', 'branch', 'creator', 'jobCard',
            'inventoryItem', 'items',
        ]);

        if ($this->wantsSalesDeskReturn($request) || $this->wantsProductionFloorReturn($request)) {
            return view('admin.sales.desk.order-modal', compact('salesOrder'));
        }

        $salesOrder->load([
            'invoices', 'orderNotes.user', 'attachments.uploader', 'conversion.converter',
            'items.productionSpecification.paperInventoryItem',
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
        ) + [
            'catalogueItems' => InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sku', 'stock_role']),
        ]);
    }

    public function edit(Request $request, SalesOrder $salesOrder): View
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

        $payload = [
            'salesOrder' => $salesOrder,
            'customerArtworks' => $customerArtworks,
            'catalogueItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sku']),
            ...$this->formMeta(),
        ];

        if ($this->wantsSalesDeskReturn($request)) {
            return view('admin.sales.desk.order-edit-modal', $payload);
        }

        return view('admin.sales.orders.edit', $payload);
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

        $redirect = redirect()->route('admin.sales-orders.show', $salesOrder);

        if ($this->wantsSalesDeskReturn($request)) {
            $redirect = redirect()->route('admin.sales.desk', [
                'customer' => $salesOrder->customer?->getRouteKey(),
                'order' => $salesOrder->fresh()->getRouteKey(),
                'step' => 4,
            ]);
        }

        return $this->modalOrRedirect(
            __('Sales order updated.'),
            $redirect,
        );
    }

    public function updateProductionSetup(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('updateProductionSetup', $salesOrder);

        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
        ]);

        $item = InventoryItem::query()->forTenant()->findOrFail($validated['inventory_item_id']);

        $salesOrder->update(['inventory_item_id' => $item->id]);

        if ($salesOrder->jobCard) {
            $salesOrder->jobCard->update(['inventory_item_id' => $item->id]);
        }

        return back()->with('status', __('Production product linked to this order.'));
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

    public function releaseToProduction(Request $request, SalesOrder $salesOrder, SalesOrderWorkflowService $workflow): RedirectResponse|JsonResponse
    {
        $this->authorize('production', $salesOrder);

        try {
            $workflow->releaseToProduction($salesOrder, (int) auth()->id());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            $firstError = collect($exception->errors())->flatten()->first();

            return back()
                ->withErrors($exception->errors())
                ->with('error', $firstError);
        }

        $deskRedirect = $this->wantsSalesDeskReturn($request)
            ? route('admin.sales.desk', [
                'customer' => $salesOrder->customer?->getRouteKey() ?? $salesOrder->customer_id,
                'order' => $salesOrder->fresh()->getRouteKey(),
                'step' => 4,
            ])
            : null;

        if ($deskRedirect !== null && ($request->expectsJson() || $request->ajax())) {
            $salesOrder->loadMissing('jobCard.queues.workCenter');
            $workCenter = $salesOrder->jobCard?->queues->sortBy('queue_position')->first()?->workCenter?->name;

            return response()->json([
                'ok' => true,
                'message' => $workCenter
                    ? __('Sales order sent to production queue (:work_center).', ['work_center' => $workCenter])
                    : __('Sales order sent to production queue.'),
                'redirect' => $deskRedirect,
            ]);
        }

        if ($deskRedirect !== null) {
            return redirect()
                ->to($deskRedirect)
                ->with('status', __('Sales order sent to production.'));
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

        $eligibleQuotations = Quotation::query()
            ->forTenant()
            ->selectableForSalesOrderPicker()
            ->with('customer')
            ->orderByDesc('quotation_date')
            ->get();

        return [
            'formFields' => $this->formSettings->resolvedFields('sales_order', $companyId, $branchId),
            'eligibleQuotations' => $eligibleQuotations,
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'public_id', 'company_name']),
        ];
    }
}
