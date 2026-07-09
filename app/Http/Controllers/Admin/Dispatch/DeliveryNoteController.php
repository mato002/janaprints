<?php

namespace App\Http\Controllers\Admin\Dispatch;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Services\Accounting\DeliveryInvoiceEligibilityService;
use App\Services\Dispatch\DeliveryNoteAuthority;
use App\Services\Dispatch\DeliveryNoteService;
use App\Support\Sales\CustomerInvoiceCreationAuthority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryNoteController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected DeliveryNoteAuthority $deliveryAuthority,
        protected DeliveryNoteService $deliveryNotes,
        protected DeliveryInvoiceEligibilityService $invoiceEligibility,
        protected CustomerInvoiceCreationAuthority $invoiceAuthority,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DeliveryNote::class);

        $notes = $this->scopeToTenant(
            DeliveryNote::query()
                ->with(['customer:id,company_name', 'productionJobCard:id,job_card_number', 'salesOrder:id,order_number'])
        )
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('delivery_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dispatch.delivery-notes.index', [
            'notes' => $notes,
            'filterStatus' => $request->query('status'),
        ]);
    }

    public function show(DeliveryNote $deliveryNote): View
    {
        $this->authorize('view', $deliveryNote);

        $deliveryNote->load([
            'items',
            'customer',
            'salesOrder',
            'productionJobCard',
            'dispatcher:id,name',
            'deliverer:id,name',
            'activeInvoice',
            'invoicer:id,name',
            'postedJournal',
        ]);

        $invoiceEligibility = $this->invoiceEligibility->check($deliveryNote);
        $partialDelivery = $this->invoiceEligibility->partialDeliverySummary($deliveryNote);
        $inventoryImpact = app(\App\Services\Dispatch\DispatchInventoryReportService::class)->inventoryImpact($deliveryNote);
        $dispatchReadiness = app(\App\Services\Dispatch\DispatchInventoryService::class)->dispatchReadiness($deliveryNote);

        $salesOrderInvoices = collect();
        if ($deliveryNote->sales_order_id) {
            $salesOrderInvoices = CustomerInvoice::query()
                ->where('sales_order_id', $deliveryNote->sales_order_id)
                ->where('company_id', $deliveryNote->company_id)
                ->whereNot('status', \App\Enums\CustomerInvoiceStatus::Cancelled)
                ->orderByDesc('invoice_date')
                ->orderByDesc('id')
                ->get(['id', 'invoice_number', 'delivery_note_id', 'status', 'total_amount']);
        }

        return view('admin.dispatch.delivery-notes.show', [
            'note' => $deliveryNote,
            'invoiceEligibility' => $invoiceEligibility,
            'partialDelivery' => $partialDelivery,
            'inventoryImpact' => $inventoryImpact,
            'dispatchReadiness' => $dispatchReadiness,
            'salesOrderInvoices' => $salesOrderInvoices,
        ]);
    }

    public function generateInvoice(DeliveryNote $deliveryNote): RedirectResponse
    {
        $this->authorize('create', CustomerInvoice::class);
        abort_unless($deliveryNote->company_id === (int) tenant()->companyId(), 403);

        $eligibility = $this->invoiceEligibility->check($deliveryNote);
        if (! $eligibility['eligible']) {
            return back()->withErrors([
                'delivery_note' => implode(' ', $eligibility['blockers']),
            ]);
        }

        $result = $this->invoiceAuthority->createFromDeliveryNote($deliveryNote, (int) auth()->id());

        $flash = $result->wasExisting
            ? ($result->message ?? __('Existing invoice opened.'))
            : __('Invoice :number created from delivery note.', ['number' => $result->invoice->invoice_number]);

        return redirect()
            ->route('admin.invoices.show', $result->invoice)
            ->with('status', $flash);
    }

    public function storeFromJob(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('create', DeliveryNote::class);
        abort_unless($jobCard->company_id === (int) tenant()->companyId(), 403);

        $validated = $request->validate([
            'delivery_date' => ['nullable', 'date'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'dispatch_notes' => ['nullable', 'string'],
        ]);

        $result = $this->deliveryAuthority->createDraftFromJobCard($jobCard, $validated);

        $flash = $result->wasExisting
            ? ($result->message ?? __('Existing delivery note opened.'))
            : __('Delivery note :number created.', ['number' => $result->note->delivery_note_number]);

        return redirect()
            ->route('admin.dispatch.delivery-notes.show', $result->note)
            ->with('status', $flash);
    }

    public function dispatch(Request $request, DeliveryNote $deliveryNote): RedirectResponse
    {
        $this->authorize('dispatch', $deliveryNote);

        $validated = $request->validate([
            'dispatch_notes' => ['nullable', 'string'],
        ]);

        $this->deliveryNotes->dispatch(
            $deliveryNote,
            (int) auth()->id(),
            $validated['dispatch_notes'] ?? null,
        );

        return back()->with('status', __('Delivery note dispatched.'));
    }

    public function deliver(Request $request, DeliveryNote $deliveryNote): RedirectResponse
    {
        $this->authorize('deliver', $deliveryNote);

        $validated = $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'recipient_signature' => ['nullable', 'string'],
            'delivery_notes' => ['nullable', 'string'],
        ]);

        $this->deliveryNotes->deliver($deliveryNote, (int) auth()->id(), $validated);

        return back()->with('status', __('Delivery confirmed. Record is now immutable.'));
    }

    public function cancel(Request $request, DeliveryNote $deliveryNote): RedirectResponse
    {
        $this->authorize('cancel', $deliveryNote);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->deliveryNotes->cancel($deliveryNote, $validated['reason'] ?? null);

        return back()->with('status', __('Delivery note cancelled.'));
    }
}
