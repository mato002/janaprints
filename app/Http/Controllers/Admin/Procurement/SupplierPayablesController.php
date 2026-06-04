<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\SupplierAgingService;
use App\Support\Procurement\SupplierLedgerService;
use App\Support\Procurement\SupplierStatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierPayablesController extends Controller
{
    use ScopesToTenant;

    public function ledger(Request $request, SupplierLedgerService $ledger): View
    {
        $this->authorize('viewPayablesLedger', Vendor::class);

        $vendors = $this->scopeToTenant(Vendor::query())->orderBy('vendor_name')->get(['id', 'vendor_name']);
        $report = null;
        $vendorId = $request->integer('vendor_id');

        if ($vendorId) {
            $vendor = Vendor::query()->forTenant()->findOrFail($vendorId);
            $this->authorize('view', $vendor);

            $report = $ledger->build([
                'vendor_id' => $vendorId,
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ]);
            $report['vendor'] = $vendor;
        }

        return view('admin.payables.ledger', compact('vendors', 'report', 'vendorId'));
    }

    public function statement(Request $request, SupplierStatementService $statements): View
    {
        $this->authorize('viewPayablesStatement', Vendor::class);

        $vendors = $this->scopeToTenant(Vendor::query())->orderBy('vendor_name')->get(['id', 'vendor_name']);
        $report = null;

        if ($request->filled('vendor_id') && $request->filled('from_date') && $request->filled('to_date')) {
            $report = $statements->build([
                'vendor_id' => $request->integer('vendor_id'),
                'from_date' => $request->string('from_date')->toString(),
                'to_date' => $request->string('to_date')->toString(),
            ]);
        }

        return view('admin.payables.statement', compact('vendors', 'report'));
    }

    public function aging(Request $request, SupplierAgingService $aging): View
    {
        $this->authorize('viewPayablesAging', Vendor::class);

        $report = $aging->build([
            'vendor_id' => $request->integer('vendor_id') ?: null,
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
        ]);

        $vendors = $this->scopeToTenant(Vendor::query())->orderBy('vendor_name')->get(['id', 'vendor_name']);

        return view('admin.payables.aging', compact('report', 'vendors'));
    }
}
