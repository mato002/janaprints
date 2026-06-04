<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Enums\TaxDirection;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use App\Support\Tax\TaxTransactionRecorder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxLedgerController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected TaxTransactionRecorder $recorder,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewLedger', TaxCode::class);

        ['companyId' => $companyId] = $this->tenantIds();

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        $periodId = $request->integer('tax_period_id') ?: null;

        if ($periodId) {
            $period = TaxPeriod::query()->forTenant()->find($periodId);
            if ($period) {
                $from = $period->start_date->toDateString();
                $to = $period->end_date->toDateString();
            }
        }

        $direction = $request->input('direction')
            ? TaxDirection::from($request->input('direction'))
            : null;

        $rows = $this->recorder->ledgerQuery($companyId, $from, $to, $direction);

        return view('admin.tax.ledger.index', [
            'rows' => $rows,
            'periods' => TaxPeriod::query()->forTenant()->orderByDesc('start_date')->get(),
            'filters' => [
                'from_date' => $from,
                'to_date' => $to,
                'tax_period_id' => $periodId,
                'direction' => $request->input('direction'),
            ],
        ]);
    }
}
