<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Reports\HrReportExporter;
use App\Support\Reports\HrReportPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrReportController extends Controller
{
    public function __construct(
        protected HrReportPresenter $presenter,
        protected HrReportExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('hr.reports.view') || $request->user()?->can('reports.view'),
            403,
        );

        return view('admin.reports.hr.index', $this->presenter->present($request));
    }

    public function print(Request $request): View
    {
        abort_unless(
            $request->user()?->can('hr.reports.view') || $request->user()?->can('reports.view'),
            403,
        );

        return view('admin.reports.hr.print', $this->presenter->present($request));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()?->can('hr.reports.export') || $request->user()?->can('reports.export'),
            403,
        );

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        return $this->exporter->download($request, $format);
    }
}
