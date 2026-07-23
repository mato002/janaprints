<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesInvoiceItems;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\CustomerInvoiceCreationAuthority;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\ReturnsToSalesDesk;
use App\Support\Sales\SalesDocumentEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    use ManagesInvoiceItems, ResolvesCrmTenant, ReturnsToSalesDesk, ScopesToTenant;

    public function __construct(
        protected CustomerInvoiceService $invoices,
        protected CustomerInvoiceCreationAuthority $invoiceAuthority,
        protected SalesDocumentEmailService $documentEmails,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CustomerInvoice::class);

        $invoices = $this->scopeToTenant(
            CustomerInvoice::query()
                ->with(['customer', 'salesOrder'])
                ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
        )->latest('invoice_date')->latest('id')->paginate(20);

        return view('admin.sales.invoices.index', compact('invoices'));
    }

    public function creditNotesIndex(): View
    {
        $this->authorize('viewAny', CustomerInvoice::class);

        $creditNotes = $this->scopeToTenant(
            CustomerInvoice::query()
                ->with(['customer', 'salesOrder', 'creditedInvoice'])
                ->where('invoice_type', CustomerInvoiceType::CreditNote)
        )->latest('invoice_date')->latest('id')->paginate(20);

        return view('admin.sales.invoices.credit-notes-index', ['creditNotes' => $creditNotes]);
    }

    public function show(Request $request, CustomerInvoice $invoice): View
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

        if ($this->wantsSalesDeskReturn($request)) {
            return view('admin.sales.desk.invoice-show-modal', compact('invoice'));
        }

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

        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Approved)) {
            return back()->withErrors([
                'workflow' => __('Invoice cannot be approved from its current status.'),
            ]);
        }

        $this->invoices->approve($invoice, (int) auth()->id());

        return back()->with('status', __('Invoice approved.'));
    }

    public function post(CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('post', $invoice);

        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Posted)) {
            return back()->withErrors([
                'workflow' => __('Only approved invoices can be posted.'),
            ]);
        }

        $this->invoices->post($invoice, (int) auth()->id());

        return back()->with('status', __('Invoice posted to accounts receivable.'));
    }

    public function cancel(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        if (! $invoice->status->canTransitionTo(CustomerInvoiceStatus::Cancelled)) {
            return back()->withErrors([
                'workflow' => __('Invoice cannot be cancelled from its current status.'),
            ]);
        }

        $validated = $request->validate(['cancel_reason' => ['nullable', 'string', 'max:255']]);

        $this->invoices->cancel($invoice, (int) auth()->id(), $validated['cancel_reason'] ?? null);

        return back()->with('status', __('Invoice cancelled.'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CustomerInvoice::class);

        $search = trim((string) $request->query('search', ''));
        $customerId = $request->integer('customer_id') ?: null;

        $query = $this->scopeToTenant(
            SalesOrder::query()
                ->with(['customer'])
                ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
        );

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('order_number', 'like', $like)
                    ->orWhereHas('customer', fn ($customer) => $customer->where('company_name', 'like', $like));
            });
        }

        $orders = $query
            ->latest('order_date')
            ->latest('id')
            ->limit(100)
            ->get()
            ->filter(fn (SalesOrder $order) => $order->remainingInvoiceTotal() > 0)
            ->values();

        return view('admin.sales.invoices.select-order', [
            'orders' => $orders,
            'search' => $search,
            'customerId' => $customerId,
        ]);
    }

    public function createFromSalesOrder(Request $request, SalesOrder $salesOrder): View
    {
        $this->authorize('create', CustomerInvoice::class);
        $this->authorize('view', $salesOrder);

        $salesOrder->load('items', 'customer', 'jobCard');

        $billingEligibilityService = app(\App\Support\Sales\SalesOrderBillingEligibilityService::class);

        $payload = [
            'salesOrder' => $salesOrder,
            'defaultTaxRate' => config('settings_registry.sections.company.settings.default_tax_rate.default', 16),
            'pendingInvoices' => $salesOrder->invoices()
                ->whereIn('status', [
                    CustomerInvoiceStatus::Draft,
                    CustomerInvoiceStatus::Approved,
                ])
                ->orderBy('id')
                ->get(['id', 'invoice_number', 'invoice_type', 'total_amount', 'status']),
            'billingEligibilityByType' => [
                'standard' => $billingEligibilityService->assess($salesOrder, CustomerInvoiceType::Standard),
                'partial' => $billingEligibilityService->assess($salesOrder, CustomerInvoiceType::Partial),
                'deposit' => $billingEligibilityService->assess($salesOrder, CustomerInvoiceType::Deposit),
                'progress' => $billingEligibilityService->assess($salesOrder, CustomerInvoiceType::Progress),
            ],
        ];

        if ($this->wantsSalesDeskReturn($request)) {
            return view('admin.sales.desk.invoice-modal', $payload);
        }

        return view('admin.sales.invoices.create-from-order', $payload);
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

        $result = $this->invoiceAuthority->createFromSalesOrder($salesOrder, (int) auth()->id(), [
            'invoice_type' => $type,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'billing_percent' => $type === CustomerInvoiceType::Progress ? ($validated['billing_percent'] ?? null) : null,
            'deposit_amount' => $type === CustomerInvoiceType::Deposit ? ($validated['deposit_amount'] ?? null) : null,
            'lines' => $lines,
        ]);

        $flash = $result->wasExisting
            ? ($result->message ?? __('Existing invoice opened.'))
            : __('Invoice created from sales order.');

        if ($this->wantsSalesDeskReturn($request)) {
            return redirect()->route('admin.sales.desk', [
                'customer' => $salesOrder->customer?->getRouteKey(),
                'order' => $salesOrder->getRouteKey(),
                'step' => 4,
            ])->with('status', $flash);
        }

        return redirect()
            ->route('admin.invoices.show', $result->invoice)
            ->with('status', $flash);
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

        $result = $this->invoiceAuthority->createFromJobCard($jobCard, (int) auth()->id(), [
            'invoice_type' => CustomerInvoiceType::from($validated['invoice_type'] ?? 'standard'),
            'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
            'billing_percent' => $validated['billing_percent'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
        ]);

        $flash = $result->wasExisting
            ? ($result->message ?? __('Existing invoice opened.'))
            : __('Invoice created from job card.');

        return redirect()
            ->route('admin.invoices.show', $result->invoice)
            ->with('status', $flash);
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
