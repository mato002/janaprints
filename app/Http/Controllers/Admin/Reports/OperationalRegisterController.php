<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Reports\OperationalRegisterExporter;
use App\Support\Reports\OperationalRegisterPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalRegisterController extends Controller
{
    public function __construct(
        protected OperationalRegisterPresenter $presenter,
        protected OperationalRegisterExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view')
            || $request->user()?->can('intelligence.production.view')
            || $request->user()?->can('production.queue.view'), 403);

        return view('admin.reports.operational-registers.index', $this->presenter->present($request));
    }

    public function print(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        return view('admin.reports.operational-registers.print', $this->presenter->present($request));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('reports.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        return $this->exporter->download($request, $format);
    }
}
