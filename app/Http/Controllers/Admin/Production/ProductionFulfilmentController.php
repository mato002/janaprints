<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\ProductionFulfilmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductionFulfilmentController extends Controller
{
    public function __construct(
        protected ProductionFulfilmentService $fulfilment,
    ) {}

    public function markReadyForCollection(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('fulfil', $jobCard);

        $validated = $request->validate([
            'collection_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->fulfilment->markReadyForCollection($jobCard, (int) auth()->id(), $validated);

        return back()->with('status', __('Marked ready for collection.'));
    }

    public function createDelivery(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('fulfil', $jobCard);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'dispatch_date' => ['nullable', 'date'],
        ]);

        $this->fulfilment->createAndDispatchDelivery($jobCard, (int) auth()->id(), $validated);

        return back()->with('status', __('Delivery created and dispatched.'));
    }

    public function confirmCollection(Request $request, ProductionJobCard $jobCard, ProductionFulfilment $fulfilment): RedirectResponse
    {
        $this->authorize('fulfil', $jobCard);
        abort_unless($fulfilment->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'collected_by_name' => ['required', 'string', 'max:120'],
            'collector_id_number' => ['nullable', 'string', 'max:60'],
            'collector_phone' => ['nullable', 'string', 'max:30'],
            'collected_at' => ['nullable', 'date'],
            'collection_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->fulfilment->confirmCollection($fulfilment, (int) auth()->id(), $validated);

        return back()->with('status', __('Collection confirmed.'));
    }

    public function prepareDelivery(Request $request, ProductionJobCard $jobCard, ProductionFulfilment $fulfilment): RedirectResponse
    {
        $this->authorize('fulfil', $jobCard);
        abort_unless($fulfilment->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'dispatch_date' => ['nullable', 'date'],
        ]);

        $this->fulfilment->prepareDelivery($fulfilment, $validated);

        return back()->with('status', __('Delivery details saved.'));
    }

    public function confirmDelivery(Request $request, ProductionJobCard $jobCard, ProductionFulfilment $fulfilment): RedirectResponse
    {
        $this->authorize('fulfil', $jobCard);
        abort_unless($fulfilment->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'received_by' => ['required', 'string', 'max:120'],
            'delivered_at' => ['nullable', 'date'],
            'signature_name' => ['nullable', 'string', 'max:120'],
            'delivery_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->fulfilment->confirmDelivery($fulfilment, (int) auth()->id(), $validated);

        return back()->with('status', __('Delivery confirmed.'));
    }
}
