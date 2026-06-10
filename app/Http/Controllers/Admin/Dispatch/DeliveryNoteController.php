<?php

namespace App\Http\Controllers\Admin\Dispatch;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Services\Accounting\DeliveryInvoiceEligibilityService;
use App\Services\Dispatch\DeliveryNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryNoteController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected DeliveryNoteService $deliveryNotes,
        protected DeliveryInvoiceEligibilityService $invoiceEligibility,
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

        return view('admin.dispatch.delivery-notes.show', [
            'note' => $deliveryNote,
            'invoiceEligibility' => $invoiceEligibility,
            'partialDelivery' => $partialDelivery,
            'inventoryImpact' => $inventoryImpact,
        ]);
    }

    public function generateInvoice(DeliveryNote $deliveryNote): RedirectResponse
    {
        abort(501, __('Invoice generation from delivery notes is not available in this build.'));
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

        $note = $this->deliveryNotes->createDraftFromJobCard($jobCard, $validated);

        return redirect()
            ->route('admin.dispatch.delivery-notes.show', $note)
            ->with('status', __('Delivery note :number created.', ['number' => $note->delivery_note_number]));
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
