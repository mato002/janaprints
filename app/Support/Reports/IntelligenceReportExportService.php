<?php

namespace App\Support\Reports;

use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntelligenceReportExportService
{
    public function __construct(
        protected TabularExportWriter $writer,
        protected IntelligenceReportPresenter $legacyPresenter,
        protected ExecutiveReportPresenter $executivePresenter,
        protected KpiCenterPresenter $kpiPresenter,
        protected IntelligenceSectionExportMapper $sectionMapper,
        protected Inventory360Presenter $inventory360Presenter,
        protected Procurement360Presenter $procurement360Presenter,
        protected Branch360Presenter $branch360Presenter,
        protected Production360Presenter $production360Presenter,
        protected Financial360Presenter $financial360Presenter,
        protected Commercial360Presenter $commercial360Presenter,
    ) {}

    public function exportLegacy(string $key, Request $request, string $format): StreamedResponse
    {
        $payload = $this->legacyPresenter->present($request, $key);

        $headers = [__('Metric'), __('Value'), __('Notes')];
        $rows = collect($payload['widgets'] ?? [])
            ->map(fn (array $widget) => [
                $widget['label'] ?? '',
                $widget['value'] ?? '',
                $widget['hint'] ?? '',
            ])
            ->all();

        return $this->writer->download(
            $format,
            "report-{$key}-".now()->format('Y-m-d'),
            $headers,
            $rows,
            $payload['title'] ?? __('Report'),
            $this->filterSubtitle($payload['filters'] ?? []),
        );
    }

    public function exportExecutive(Request $request, string $format): StreamedResponse
    {
        $payload = $this->executivePresenter->present($request);

        $headers = [__('Section'), __('Metric'), __('Value')];
        $rows = [];

        foreach ($payload['widget_sections'] ?? [] as $section) {
            foreach ($section['widgets'] ?? [] as $widget) {
                $rows[] = [
                    $section['title'] ?? '',
                    $widget['label'] ?? '',
                    $widget['value'] ?? '',
                ];
            }
        }

        return $this->writer->download(
            $format,
            'executive-dashboard-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            $payload['title'] ?? __('Executive Dashboard'),
            $this->filterSubtitle($payload['filters'] ?? []),
        );
    }

    public function exportKpi(Request $request, string $format): StreamedResponse
    {
        $payload = $this->kpiPresenter->present($request);

        $headers = [__('Category'), __('KPI'), __('Value'), __('Status'), __('Source')];
        $rows = [];

        foreach ($payload['kpi_groups'] ?? [] as $groupKey => $cards) {
            $groupLabel = $payload['group_labels'][$groupKey] ?? ucfirst((string) $groupKey);

            foreach ($cards as $card) {
                $rows[] = [
                    $groupLabel,
                    $card['name'] ?? '',
                    $card['value'] ?? '',
                    $card['status_label'] ?? $card['status'] ?? '',
                    $card['source'] ?? '',
                ];
            }
        }

        return $this->writer->download(
            $format,
            'kpi-center-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            $payload['title'] ?? __('KPI Center'),
            $this->filterSubtitle($payload['filters'] ?? []),
        );
    }

    public function exportIntelligence360(string $reportKey, Request $request, string $format): StreamedResponse
    {
        $payload = $this->presentIntelligence360($reportKey, $request);

        $headers = [__('Section'), __('Metric'), __('Value')];
        $rows = $this->sectionMapper->rows($payload['sections'] ?? []);

        return $this->writer->download(
            $format,
            "{$reportKey}-360-".now()->format('Y-m-d'),
            $headers,
            $rows,
            $payload['title'] ?? __('Intelligence Report'),
            $this->filterSubtitle($payload['filters'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentIntelligence360(string $reportKey, Request $request): array
    {
        return match ($reportKey) {
            'inventory' => $this->inventory360Presenter->present($request),
            'procurement' => $this->procurement360Presenter->present($request),
            'branch' => $this->branch360Presenter->present($request),
            'production' => $this->production360Presenter->present($request),
            'financial' => $this->financial360Presenter->present($request),
            'commercial' => $this->commercial360Presenter->present($request),
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filterSubtitle(array $filters): string
    {
        $from = $filters['from_date'] ?? null;
        $to = $filters['to_date'] ?? null;

        if ($from || $to) {
            return trim(($from ?? '…').' — '.($to ?? '…'));
        }

        return '';
    }
}
