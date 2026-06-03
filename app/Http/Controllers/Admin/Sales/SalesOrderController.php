<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesSalesOrderItems;
use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\FormSettingsService;
use App\Support\QuotationConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    use ManagesSalesOrderItems, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = $this->scopeToTenant(
            SalesOrder::query()->with(['customer', 'branch', 'quotation', 'creator'])
        )->latest('order_date')->paginate(15);

        return view('admin.sales.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', SalesOrder::class);

        return view('admin.sales.orders.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $validated = $request->validate(
            $this->formSettings->mergeValidationRules('sales_order', [
                'quotation_id' => ['exists:quotations,id'],
            ], $companyId, $branchId),
        );

        $quotation = Quotation::query()->forTenant()->findOrFail($validated['quotation_id']);
        $this->authorize('view', $quotation);

        $salesOrder = QuotationConversionService::convert($quotation, (int) auth()->id());

        return redirect()
            ->route('admin.sales-orders.show', $salesOrder)
            ->with('status', __('Sales order created from quotation.'));
    }

    public function show(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load([
            'customer', 'quotation', 'artworkRequest', 'branch', 'creator', 'jobCard',
            'items', 'orderNotes.user', 'attachments.uploader', 'conversion.converter',
        ]);

        return view('admin.sales.orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder): View
    {
        $this->authorize('update', $salesOrder);

        $salesOrder->load('items');

        return view('admin.sales.orders.edit', [
            'salesOrder' => $salesOrder,
            ...$this->formMeta(),
        ]);
    }

    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        $header = $request->validate([
            'order_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
        ]);

        ['items' => $items, 'totals' => $totals] = $this->validatedItems($request);

        $salesOrder->update($header);
        $this->syncItems($salesOrder, $items, $totals);

        return redirect()
            ->route('admin.sales-orders.show', $salesOrder)
            ->with('status', __('Sales order updated.'));
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
        $salesOrder->transitionTo(SalesOrderStatus::Confirmed);

        return back()->with('status', __('Sales order confirmed.'));
    }

    public function readyForProduction(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('production', $salesOrder);
        abort_unless($salesOrder->status->canTransitionTo(SalesOrderStatus::ReadyForProduction), 403);
        $salesOrder->transitionTo(SalesOrderStatus::ReadyForProduction);

        return back()->with('status', __('Sales order marked ready for production.'));
    }

    public function startProduction(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('production', $salesOrder);
        abort_unless($salesOrder->status->canTransitionTo(SalesOrderStatus::InProduction), 403);
        $salesOrder->transitionTo(SalesOrderStatus::InProduction);

        return back()->with('status', __('Sales order in production.'));
    }

    public function complete(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('production', $salesOrder);
        abort_unless($salesOrder->status->canTransitionTo(SalesOrderStatus::Completed), 403);
        $salesOrder->transitionTo(SalesOrderStatus::Completed);

        return back()->with('status', __('Sales order completed.'));
    }

    public function deliver(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('production', $salesOrder);
        abort_unless($salesOrder->status->canTransitionTo(SalesOrderStatus::Delivered), 403);
        $salesOrder->transitionTo(SalesOrderStatus::Delivered);

        return back()->with('status', __('Sales order delivered.'));
    }

    public function close(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('close', $salesOrder);
        $salesOrder->transitionTo(SalesOrderStatus::Closed);

        return back()->with('status', __('Sales order closed.'));
    }

    public function hold(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);

        if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::OnHold)) {
            abort(403);
        }

        $salesOrder->transitionTo(SalesOrderStatus::OnHold);

        return back()->with('status', __('Sales order placed on hold.'));
    }

    public function resume(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);
        $salesOrder->transitionTo(SalesOrderStatus::Confirmed);

        return back()->with('status', __('Sales order resumed.'));
    }

    public function cancel(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('transition', $salesOrder);

        if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::Cancelled)) {
            abort(403);
        }

        $salesOrder->transitionTo(SalesOrderStatus::Cancelled);

        return back()->with('status', __('Sales order cancelled.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds(request());

        $eligible = Quotation::query()
            ->forTenant()
            ->where('status', QuotationStatus::Accepted)
            ->whereDoesntHave('salesOrder')
            ->with('customer')
            ->orderByDesc('quotation_date')
            ->get();

        return [
            'formFields' => $this->formSettings->resolvedFields('sales_order', $companyId, $branchId),
            'eligibleQuotations' => $eligible,
        ];
    }
}
