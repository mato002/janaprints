<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Reports\ProductionReportExporter;
use App\Support\Reports\ProductionReportPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionReportController extends Controller
{
    public function __construct(
        protected ProductionReportPresenter $presenter,
        protected ProductionReportExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        return view('admin.reports.production.index', $this->presenter->present($request));
    }

    public function print(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        $payload = $this->presenter->present($request);

        return view('admin.reports.production.print', $payload);
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        abort_unless($request->user()?->can('reports.export'), 403);

        if ($request->boolean('schedule')) {
            return $this->scheduleExport($request);
        }

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        return $this->exporter->download($request, $format);
    }

    protected function scheduleExport(Request $request): RedirectResponse
    {
        $frequency = (string) $request->input('frequency', 'weekly');
        $valid = array_keys(config('production_reports.schedule_frequencies', []));

        if (! in_array($frequency, $valid, true)) {
            $frequency = 'weekly';
        }

        $user = $request->user();
        $companyId = tenant()->companyId() ?? $user?->company_id;

        Cache::put(
            "production_report_schedule:{$companyId}:{$user?->id}",
            [
                'frequency' => $frequency,
                'format' => $request->input('format', 'csv'),
                'tab' => $request->input('tab', 'throughput'),
                'filters' => $request->only(['from_date', 'to_date', 'branch_id', 'tab']),
                'scheduled_at' => now()->toIso8601String(),
            ],
            now()->addYear(),
        );

        return redirect()
            ->route('admin.reports.production', $request->only(['from_date', 'to_date', 'branch_id', 'tab']))
            ->with('status', __('Scheduled :frequency export saved. Reports will be generated on the next run.', [
                'frequency' => __(config("production_reports.schedule_frequencies.{$frequency}", $frequency)),
            ]));
    }
}
