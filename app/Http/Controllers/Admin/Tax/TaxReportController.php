<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use App\Support\Tax\TaxReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxReportController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected TaxReportService $reports,
    ) {}

    public function vatSummary(Request $request): View
    {
        $this->authorize('viewReports', TaxCode::class);

        $filters = $this->filters($request);
        $report = $this->reports->vatSummary($filters);

        return view('admin.tax.reports.vat-summary', [
            'report' => $report,
            'filters' => $filters,
            'periods' => TaxPeriod::query()->forTenant()->orderByDesc('start_date')->get(),
        ]);
    }

    public function outputVat(Request $request): View
    {
        $this->authorize('viewReports', TaxCode::class);

        $filters = $this->filters($request);
        $report = $this->reports->outputVat($filters);

        return view('admin.tax.reports.output-vat', [
            'report' => $report,
            'filters' => $filters,
            'periods' => TaxPeriod::query()->forTenant()->orderByDesc('start_date')->get(),
        ]);
    }

    public function inputVat(Request $request): View
    {
        $this->authorize('viewReports', TaxCode::class);

        $filters = $this->filters($request);
        $report = $this->reports->inputVat($filters);

        return view('admin.tax.reports.input-vat', [
            'report' => $report,
            'filters' => $filters,
            'periods' => TaxPeriod::query()->forTenant()->orderByDesc('start_date')->get(),
        ]);
    }

    public function liability(Request $request): View
    {
        $this->authorize('viewReports', TaxCode::class);

        $filters = $this->filters($request);
        $report = $this->reports->taxLiability($filters);

        return view('admin.tax.reports.liability', [
            'report' => $report,
            'filters' => $filters,
            'periods' => TaxPeriod::query()->forTenant()->orderByDesc('start_date')->get(),
        ]);
    }

    /**
     * @return array{company_id: int, from_date?: string, to_date?: string, tax_period_id?: int}
     */
    protected function filters(Request $request): array
    {
        ['companyId' => $companyId] = $this->tenantIds();

        $periodId = $request->integer('tax_period_id') ?: null;
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        if ($periodId) {
            $period = TaxPeriod::query()->forTenant()->find($periodId);
            if ($period) {
                $from = $period->start_date->toDateString();
                $to = $period->end_date->toDateString();
            }
        }

        return array_filter([
            'company_id' => $companyId,
            'from_date' => $from,
            'to_date' => $to,
            'tax_period_id' => $periodId,
        ]);
    }
}
