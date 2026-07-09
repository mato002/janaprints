<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Services\Client\ClientPortalOrderTrackingService;
use Illuminate\View\View;

class ClientJobController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected ClientPortalOrderTrackingService $tracking,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $jobs = ProductionJobCard::query()
            ->where('customer_id', $customer->id)
            ->with(['salesOrder', 'fulfilment'])
            ->latest('id')
            ->paginate(12);

        $jobs->getCollection()->transform(function (ProductionJobCard $jobCard) {
            if ($jobCard->salesOrder) {
                $jobCard->tracking_summary = $this->tracking->track($jobCard->salesOrder);
            }

            return $jobCard;
        });

        return view('client.jobs.index', compact('customer', 'jobs'));
    }

    public function show(ProductionJobCard $jobCard): View
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($jobCard, $customer);

        $jobCard->load(['salesOrder.items', 'salesOrder.invoices', 'fulfilment']);

        $tracking = $jobCard->salesOrder
            ? $this->tracking->track($jobCard->salesOrder)
            : null;

        $deliveryNotes = DeliveryNote::query()
            ->where('customer_id', $customer->id)
            ->where('production_job_card_id', $jobCard->id)
            ->latest('id')
            ->limit(5)
            ->get(['id', 'delivery_note_number', 'status', 'delivery_date', 'dispatched_at']);

        return view('client.jobs.show', compact('customer', 'jobCard', 'tracking', 'deliveryNotes'));
    }
}
