<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Journal;
use App\Support\Accounting\Dashboard\AccountingDashboardPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingDashboardController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected AccountingDashboardPresenter $presenter,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewDashboard', Journal::class);

        $tenant = $this->tenantIds();

        $dashboard = $this->presenter->build($request->user(), [
            'company_id' => $request->integer('company_id') ?: $tenant['companyId'],
            'branch_id' => $request->has('branch_id')
                ? ($request->input('branch_id') !== '' ? $request->integer('branch_id') : null)
                : $tenant['branchId'],
            'period_id' => $request->integer('period_id') ?: null,
        ]);

        return view('admin.accounting.dashboard', compact('dashboard'));
    }
}
