<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\CustomerInvoiceType;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesInvoiceItems;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\SalesDocumentEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    use ManagesInvoiceItems, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected CustomerInvoiceService $invoices,
        protected SalesDocumentEmailService $documentEmails,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CustomerInvoice::class);

        $invoices = $this->scopeToTenant(
            CustomerInvoice::query()->with(['customer', 'salesOrder'])
        )->latest('invoice_date')->latest('id')->paginate(20);

        return view('admin.sales.invoices.index', compact('invoices'));
    }

    public function show(CustomerInvoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'customer',
            'salesOrder',
            'jobCard',
            'lines.salesOrderItem',
            'taxLines',
            'creditedInvoice',
            'creditNotes',
            'postedJournal',
            'creator',
            'approver',
            'poster',
            'paymentAllocations.payment',
        ]);

        return view('admin.sales.invoices.show', compact('invoice'));
    }

    public function email(CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('emailInvoice', $invoice);

        $this->documentEmails->sendInvoice($invoice, auth()->user());

        return back()->with('status', __('Invoice emailed to customer.'));
    }

    public function edit(CustomerInvoice $invoice): View
    {
        $this->authorize('update', $invoice);

        $invoice->load('lines');

        return view('admin.sales.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $header = $request->validate([
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes' => ['nullable', 'string'],
        ]);

        ['items' => $items] = $this->validatedInvoiceItems($request);

        $this->invoices->updateDraft($invoice, $header, $items, (float) $request->input('header_discount', 0));

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('status', __('Invoice updated.'));
    }

    public function destroy(CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $this->invoices->deleteDraft($invoice);

        return redirect()
            ->route('admin.invoices.index')
            ->with('status', __('Invoice deleted.'));
    }

    public function approve(CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('approve', $invoice);

        $this->invoices->approve($invoice, (int) auth()->id());

        return back()->with('status', __('Invoice approved.'));
    }

    public function post(CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('post', $invoice);

        $this->invoices->post($invoice, (int) auth()->id());

        return back()->with('status', __('Invoice posted to accounts receivable.'));
    }

    public function cancel(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $validated = $request->validate(['cancel_reason' => ['nullable', 'string', 'max:255']]);

        $this->invoices->cancel($invoice, (int) auth()->id(), $validated['cancel_reason'] ?? null);

        return back()->with('status', __('Invoice cancelled.'));
    }

    public function createFromSalesOrder(SalesOrder $salesOrder): View
    {
        $this->authorize('create', CustomerInvoice::class);
        $this->authorize('view', $salesOrder);

        $salesOrder->load('items', 'customer');

        return view('admin.sales.invoices.create-from-order', [
            'salesOrder' => $salesOrder,
            'defaultTaxRate' => config('settings_registry.sections.company.settings.default_tax_rate.default', 16),
        ]);
    }

    public function storeFromSalesOrder(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('create', CustomerInvoice::class);
        $this->authorize('view', $salesOrder);

        $validated = $request->validate([
            'invoice_type' => ['required', 'in:standard,partial,deposit,progress'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'billing_percent' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0.01'],
            'lines' => ['nullable', 'array'],
            'lines.*.sales_order_item_id' => ['required_with:lines', 'integer'],
            'lines.*.quantity' => ['required_with:lines', 'numeric', 'min:0.001'],
        ]);

        $type = CustomerInvoiceType::from($validated['invoice_type']);
        $lines = null;

        if ($type === CustomerInvoiceType::Partial && ! empty($validated['lines'])) {
            $lines = collect($validated['lines'])
                ->filter(fn (array $line) => ! empty($line['selected']))
                ->map(fn (array $line) => [
                    'sales_order_item_id' => $line['sales_order_item_id'],
                    'quantity' => $line['quantity'] ?? null,
                ])
                ->values()
                ->all();
        }

        $invoice = $this->invoices->createFromSalesOrder($salesOrder, (int) auth()->id(), [
            'invoice_type' => $type,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'billing_percent' => $validated['billing_percent'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
            'lines' => $lines,
        ]);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('status', __('Invoice created from sales order.'));
    }

    public function storeFromJobCard(ProductionJobCard $jobCard, Request $request): RedirectResponse
    {
        $this->authorize('create', CustomerInvoice::class);

        $validated = $request->validate([
            'invoice_type' => ['nullable', 'in:standard,partial,deposit,progress'],
            'invoice_date' => ['nullable', 'date'],
            'billing_percent' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $invoice = $this->invoices->createFromJobCard($jobCard, (int) auth()->id(), [
            'invoice_type' => CustomerInvoiceType::from($validated['invoice_type'] ?? 'standard'),
            'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
            'billing_percent' => $validated['billing_percent'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
        ]);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('status', __('Invoice created from job card.'));
    }

    public function storeCreditNote(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('creditNote', $invoice);

        $validated = $request->validate([
            'invoice_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $creditNote = $this->invoices->createCreditNote($invoice, (int) auth()->id(), $validated);

        return redirect()
            ->route('admin.invoices.show', $creditNote)
            ->with('status', __('Credit note created. Approve and post when ready.'));
    }
}
