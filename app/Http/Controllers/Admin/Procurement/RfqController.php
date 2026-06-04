<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\RfqVendor;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\RFQService;
use App\Support\Procurement\VendorComparisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Rfq::class);

        $rfqs = $this->scopeToTenant(
            Rfq::query()->with(['purchaseRequest', 'awardedVendor'])->latest()
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.rfqs.index', compact('rfqs'));
    }

    public function show(Rfq $rfq): View
    {
        $this->authorize('view', $rfq);

        $rfq->load([
            'purchaseRequest',
            'items.inventoryItem',
            'vendors.vendor',
            'responses.rfqItem',
            'awardedVendor',
            'purchaseOrder',
            'comparison',
        ]);

        $comparison = VendorComparisonService::buildMatrix($rfq);

        return view('admin.procurement.rfqs.show', [
            'rfq' => $rfq,
            'comparison' => $comparison,
            'vendors' => Vendor::query()->forTenant()->where('status', 'active')->orderBy('vendor_name')->get(),
        ]);
    }

    public function storeFromRequest(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->authorize('create', Rfq::class);

        $validated = $request->validate([
            'closing_date' => ['nullable', 'date', 'after_or_equal:today'],
            'vendor_ids' => ['required', 'array', 'min:1'],
            'vendor_ids.*' => ['integer', 'exists:vendors,id'],
        ]);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $rfq = RFQService::createFromPurchaseRequest(
            $purchaseRequest,
            $this->nextNumber(DocumentType::Rfq, $companyId, $branchId),
            (int) auth()->id(),
            $validated['closing_date'] ?? null,
            $validated['vendor_ids'],
        );

        return redirect()
            ->route('admin.procurement.rfqs.show', $rfq)
            ->with('status', __('RFQ created from purchase request.'));
    }

    public function issue(Rfq $rfq): RedirectResponse
    {
        $this->authorize('manage', $rfq);
        RFQService::issue($rfq);

        return back()->with('status', __('RFQ issued to vendors.'));
    }

    public function close(Rfq $rfq): RedirectResponse
    {
        $this->authorize('manage', $rfq);
        RFQService::close($rfq);

        return back()->with('status', __('RFQ closed for comparison.'));
    }

    public function recordResponse(Request $request, Rfq $rfq, RfqVendor $rfqVendor): RedirectResponse
    {
        $this->authorize('manage', $rfq);

        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.rfq_item_id' => ['required', 'integer', 'exists:rfq_items,id'],
            'lines.*.quoted_price' => ['required', 'numeric', 'min:0'],
            'lines.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
            'lines.*.warranty' => ['nullable', 'string', 'max:255'],
            'lines.*.delivery_terms' => ['nullable', 'string', 'max:255'],
            'lines.*.comments' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $lines = $validated['lines'];
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('rfq-responses/'.$rfq->id, 'public');
            $lines[0]['attachment_path'] = $path;
        }

        RFQService::recordVendorResponse($rfqVendor, $lines);

        return back()->with('status', __('Vendor response recorded.'));
    }

    public function compare(Rfq $rfq): RedirectResponse
    {
        $this->authorize('compare', $rfq);
        VendorComparisonService::persistComparison($rfq, (int) auth()->id());
        \App\Support\Procurement\RfqQuotationSyncService::syncAll($rfq->fresh());

        return back()->with('status', __('Vendor comparison saved and supplier quotations synced.'));
    }

    public function award(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('award', $rfq);

        $validated = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
        ]);

        RFQService::award($rfq, (int) $validated['vendor_id']);

        return back()->with('status', __('Vendor awarded.'));
    }

    public function convert(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('convert', $rfq);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $order = RFQService::convertToPurchaseOrder(
            $rfq,
            $this->nextNumber(DocumentType::PurchaseOrder, $companyId, $branchId),
            (int) auth()->id(),
        );

        return redirect()
            ->route('admin.procurement.orders.show', $order)
            ->with('status', __('Purchase order created from awarded RFQ.'));
    }
}
