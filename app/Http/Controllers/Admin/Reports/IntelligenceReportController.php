<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Reports\Branch360Presenter;
use App\Support\Reports\Commercial360Presenter;
use App\Support\Reports\ExecutiveReportPresenter;
use App\Support\Reports\Financial360Presenter;
use App\Support\Reports\IntelligenceReportPresenter;
use App\Support\Reports\Inventory360Presenter;
use App\Support\Reports\KpiCenterPresenter;
use App\Support\Reports\Procurement360Presenter;
use App\Support\Reports\Production360Presenter;
use App\Support\Commercial\CommercialNavigationAlignmentService;
use Illuminate\Http\RedirectResponse;
use App\Support\Reports\Asset360IntelligencePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntelligenceReportController extends Controller
{
    public function __construct(
        protected IntelligenceReportPresenter $legacyPresenter,
        protected ExecutiveReportPresenter $executivePresenter,
        protected Inventory360Presenter $inventory360Presenter,
        protected Procurement360Presenter $procurement360Presenter,
        protected KpiCenterPresenter $kpiPresenter,
        protected Branch360Presenter $branch360Presenter,
        protected Production360Presenter $production360Presenter,
        protected Financial360Presenter $financial360Presenter,
        protected Commercial360Presenter $commercial360Presenter,
        protected Asset360IntelligencePresenter $asset360Presenter,
        protected CommercialNavigationAlignmentService $commercialNavigation,
    ) {}

    public function executive(Request $request): View
    {
        $this->authorizeReport($request, 'reports.view');

        return view('admin.reports.executive', $this->executivePresenter->present($request));
    }

    public function commercial(Request $request): View
    {
        $this->authorizeReport($request, 'reports.view');

        return view('admin.reports.commercial-hub', [
            'title' => __('Commercial Reports'),
            'description' => __('Departmental commercial analytics and Commercial 360 intelligence.'),
            'links' => $this->commercialNavigation->visibleHubLinksForUser($request->user()),
        ]);
    }

    public function production(Request $request): View
    {
        return $this->legacy('production', $request);
    }

    public function inventory(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('reports.inventory.view') || $request->user()?->can('reports.view'), 403);

        return redirect()->route('admin.inventory.reports.index', $request->query());
    }

    public function procurement(Request $request): View
    {
        return $this->legacy('procurement', $request);
    }

    public function accounting(Request $request): View
    {
        return $this->legacy('accounting', $request);
    }

    public function hr(Request $request): View
    {
        return $this->legacy('hr', $request);
    }

    public function kpi(Request $request): View
    {
        abort_unless(
            $request->user()?->can('kpi.view') || $request->user()?->can('reports.view'),
            403,
        );

        return view('admin.reports.kpi', $this->kpiPresenter->present($request));
    }

    public function inventory360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.inventory.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->inventory360Presenter->present($request));
    }

    public function procurement360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.vendor.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->procurement360Presenter->present($request));
    }

    public function branch360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.branch.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->branch360Presenter->present($request));
    }

    public function production360(Request $request): View|StreamedResponse
    {
        $this->authorizeReport($request, 'intelligence.production.view', 'reports.view');

        if ($request->query('export') === 'csv') {
            abort_unless($request->user()?->can('reports.export'), 403);

            return $this->exportProduction360Csv($request);
        }

        return view('admin.reports.production-360', $this->production360Presenter->present($request));
    }

    protected function exportProduction360Csv(Request $request): StreamedResponse
    {
        $payload = $this->production360Presenter->present($request);

        return response()->streamDownload(function () use ($payload) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Section', 'Metric', 'Value']);

            foreach ($payload['sections'] as $section) {
                $title = $section['title'] ?? 'Section';

                if (($section['type'] ?? '') === 'kpis') {
                    foreach ($section['items'] ?? [] as $item) {
                        fputcsv($out, [$title, $item['label'] ?? '', $item['value'] ?? '']);
                    }
                }

                if (($section['type'] ?? '') === 'drilldown') {
                    foreach ($section['rows'] ?? [] as $row) {
                        fputcsv($out, [$title, implode(' | ', $row['cells'] ?? []), '']);
                    }
                }

                if (($section['type'] ?? '') === 'split') {
                    foreach ($section['kpis'] ?? [] as $item) {
                        fputcsv($out, [$title, $item['label'] ?? '', $item['value'] ?? '']);
                    }
                    foreach ($section['tables'] ?? [] as $table) {
                        foreach ($table['rows'] ?? [] as $row) {
                            fputcsv($out, [$table['title'] ?? $title, implode(' | ', $row['cells'] ?? []), '']);
                        }
                    }
                }
            }

            fclose($out);
        }, 'production-360-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function financial360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.financial.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->financial360Presenter->present($request));
    }

    public function commercial360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.commercial.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->commercial360Presenter->present($request));
    }

    public function asset360(Request $request): View
    {
        $this->authorizeReport($request, 'intelligence.assets.view', 'assets.analytics.view', 'reports.view');

        return view('admin.reports.intelligence-360', $this->asset360Presenter->present($request));
    }

    protected function legacy(string $key, Request $request): View
    {
        $this->authorizeReport($request, 'reports.view');

        abort_unless($this->legacyPresenter->exists($key), 404);

        return view('admin.reports.show', $this->legacyPresenter->present($request, $key));
    }

    protected function authorizeReport(Request $request, string ...$permissions): void
    {
        $user = $request->user();

        abort_unless($user, 403);

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return;
            }
        }

        abort(403);
    }
}
