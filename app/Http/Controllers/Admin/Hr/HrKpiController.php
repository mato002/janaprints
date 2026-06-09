<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Support\Hr\HrKpiExporter;
use App\Support\Hr\HrKpiPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrKpiController extends Controller
{
    public function __construct(
        protected HrKpiPresenter $presenter,
        protected HrKpiExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('hr.kpi.view') || $request->user()?->can('kpi.view'),
            403,
        );

        return view('admin.hr.kpi.index', $this->presenter->present($request));
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        abort_unless(
            $request->user()?->can('hr.kpi.export') || $request->user()?->can('reports.export'),
            403,
        );

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        if ($format === 'pdf') {
            return redirect()->route('admin.hr.kpi', $request->query());
        }

        $rows = $this->exporter->rows($request);
        $extension = $format === 'excel' ? 'xls' : 'csv';
        $mime = $format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';

        return response()->streamDownload(function () use ($rows, $format) {
            $out = fopen('php://output', 'w');

            if ($format === 'excel') {
                fwrite($out, "\xEF\xBB\xBF");
            }

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, 'hr-kpi-'.now()->format('Y-m-d').'.'.$extension, [
            'Content-Type' => $mime,
        ]);
    }
}
