<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Support\Sales\CustomerAgingService;
use App\Support\Sales\CustomerLedgerService;
use App\Support\Sales\CustomerStatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReceivablesController extends Controller
{
    use ScopesToTenant;

    public function ledger(Request $request, CustomerLedgerService $ledger): View
    {
        $this->authorize('viewReceivablesLedger', Customer::class);

        $customers = $this->scopeToTenant(Customer::query())->orderBy('company_name')->get(['id', 'company_name']);
        $customerId = $request->integer('customer_id');
        $report = null;

        if ($customerId) {
            $customer = Customer::query()->forTenant()->findOrFail($customerId);
            $this->authorize('view', $customer);

            $report = $ledger->build([
                'customer_id' => $customerId,
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ]);
            $report['customer'] = $customer;
        }

        return view('admin.sales.receivables.ledger', compact('customers', 'report', 'customerId'));
    }

    public function statement(Request $request, CustomerStatementService $statements): View
    {
        $this->authorize('viewReceivablesStatement', Customer::class);

        $customers = $this->scopeToTenant(Customer::query())->orderBy('company_name')->get(['id', 'company_name']);
        $report = null;

        if ($request->filled('customer_id') && $request->filled('from_date') && $request->filled('to_date')) {
            $report = $statements->build([
                'customer_id' => $request->integer('customer_id'),
                'from_date' => $request->string('from_date')->toString(),
                'to_date' => $request->string('to_date')->toString(),
            ]);
        }

        return view('admin.sales.receivables.statement', compact('customers', 'report'));
    }

    public function aging(Request $request, CustomerAgingService $aging): View
    {
        $this->authorize('viewReceivablesAging', Customer::class);

        $report = $aging->build([
            'customer_id' => $request->integer('customer_id') ?: null,
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
        ]);

        $customers = $this->scopeToTenant(Customer::query())->orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.sales.receivables.aging', compact('report', 'customers'));
    }
}
