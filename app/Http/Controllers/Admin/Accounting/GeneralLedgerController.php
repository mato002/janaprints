<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Support\Accounting\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralLedgerController extends Controller
{
    public function __construct(
        protected GeneralLedgerService $ledger,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Journal::class);

        $filters = [
            'period_id' => $request->integer('period_id') ?: null,
            'account_id' => $request->integer('account_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $entries = $this->ledger->entries(array_filter($filters));

        $periods = AccountingPeriod::query()->forTenant()->orderByDesc('start_date')->get();
        $accounts = GlAccount::query()->forTenant()->where('is_postable', true)->orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.accounting.ledger.index', compact('entries', 'periods', 'accounts', 'filters'));
    }
}
