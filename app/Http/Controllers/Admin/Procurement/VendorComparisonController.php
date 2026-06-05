<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\RfqStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\RfqVendor;
use App\Support\Procurement\RfqAwardService;
use App\Support\Procurement\RfqQuotationSyncService;
use App\Support\Procurement\VendorComparisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorComparisonController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('procurement.vendor_comparison.view') || auth()->user()?->can('procurement.comparison.view'), 403);

        $rfqs = $this->scopeToTenant(
            Rfq::query()
                ->with(['purchaseRequest', 'awardedVendor', 'comparison.recommendedVendor'])
                ->whereIn('status', [
                    RfqStatus::AwaitingComparison,
                    RfqStatus::Closed,
                    RfqStatus::Awarded,
                    RfqStatus::ConvertedToPo,
                ])
                ->latest()
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.vendor-comparison.index', compact('rfqs'));
    }

    public function show(Request $request, Rfq $rfq): View
    {
        $this->authorize('viewComparisonWorkspace', $rfq);

        $rfq->load([
            'purchaseRequest',
            'items.inventoryItem',
            'vendors.vendor',
            'responses.rfqItem',
            'awardedVendor',
            'purchaseOrder',
            'comparison',
            'awardLines.vendor',
        ]);

        $weights = VendorComparisonService::normalizeWeights(
            $request->only(['price', 'performance', 'lead_time', 'quality']) ?: (
                $rfq->comparison?->scoring_weights ?? VendorComparisonService::defaultWeights()
            )
        );

        $workspace = VendorComparisonService::buildWorkspace($rfq, $weights);

        return view('admin.procurement.vendor-comparison.show', [
            'rfq' => $rfq,
            'workspace' => $workspace,
            'weights' => $weights,
        ]);
    }

    public function compare(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('compare', $rfq);

        $weights = $this->validatedWeights($request);

        VendorComparisonService::persistComparison($rfq, (int) auth()->id(), $request->input('notes'), $weights);
        RfqQuotationSyncService::syncAll($rfq->fresh());

        return redirect()
            ->route('admin.procurement.vendor-comparison.show', $rfq)
            ->with('status', __('Vendor comparison saved and supplier quotations synced.'));
    }

    public function award(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('award', $rfq);

        $validated = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'auto_po' => ['nullable', 'boolean'],
        ]);

        $result = RfqAwardService::awardFull(
            $rfq,
            (int) $validated['vendor_id'],
            (int) auth()->id(),
            $request->boolean('auto_po', true),
        );

        $order = $result['purchase_orders']->first();

        if ($order) {
            return redirect()
                ->route('admin.procurement.orders.show', $order)
                ->with('status', __('Supplier awarded and purchase order generated automatically.'));
        }

        return redirect()
            ->route('admin.procurement.vendor-comparison.show', $result['rfq'])
            ->with('status', __('Supplier awarded.'));
    }

    public function awardPartial(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('award', $rfq);

        $validated = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.rfq_item_id' => ['required', 'integer', 'exists:rfq_items,id'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'auto_po' => ['nullable', 'boolean'],
        ]);

        $result = RfqAwardService::awardPartial(
            $rfq,
            (int) $validated['vendor_id'],
            $validated['lines'],
            (int) auth()->id(),
            $request->boolean('auto_po', true),
        );

        $order = $result['purchase_orders']->first();

        if ($order) {
            return redirect()
                ->route('admin.procurement.orders.show', $order)
                ->with('status', __('Partial award recorded and purchase order generated.'));
        }

        return back()->with('status', __('Partial award recorded.'));
    }

    public function splitAward(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('award', $rfq);

        $validated = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'allocations.*.rfq_item_id' => ['required', 'integer', 'exists:rfq_items,id'],
            'allocations.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'auto_po' => ['nullable', 'boolean'],
        ]);

        $result = RfqAwardService::splitAward(
            $rfq,
            $validated['allocations'],
            (int) auth()->id(),
            $request->boolean('auto_po', true),
        );

        if ($result['purchase_orders']->count() > 1) {
            return redirect()
                ->route('admin.procurement.vendor-comparison.show', $result['rfq'])
                ->with('status', __('Split award completed. :count purchase orders generated.', [
                    'count' => $result['purchase_orders']->count(),
                ]));
        }

        $order = $result['purchase_orders']->first();
        if ($order) {
            return redirect()
                ->route('admin.procurement.orders.show', $order)
                ->with('status', __('Split award completed and purchase order generated.'));
        }

        return back()->with('status', __('Split award recorded.'));
    }

    public function reject(Rfq $rfq, RfqVendor $rfqVendor): RedirectResponse
    {
        $this->authorize('manageComparison', $rfq);
        abort_unless($rfqVendor->rfq_id === $rfq->id, 404);

        RfqAwardService::rejectQuote($rfqVendor);

        return back()->with('status', __('Supplier quote rejected.'));
    }

    public function requote(Rfq $rfq, RfqVendor $rfqVendor): RedirectResponse
    {
        $this->authorize('manageComparison', $rfq);
        abort_unless($rfqVendor->rfq_id === $rfq->id, 404);

        RfqAwardService::requestRequote($rfqVendor);

        return back()->with('status', __('Requote requested from supplier.'));
    }

    /**
     * @return array<string, int>
     */
    protected function validatedWeights(Request $request): array
    {
        $validated = $request->validate([
            'price' => ['nullable', 'integer', 'min:0', 'max:100'],
            'performance' => ['nullable', 'integer', 'min:0', 'max:100'],
            'lead_time' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quality' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        return VendorComparisonService::normalizeWeights(
            collect($validated)->filter(fn ($value) => $value !== null)->all()
                ?: VendorComparisonService::defaultWeights()
        );
    }
}
