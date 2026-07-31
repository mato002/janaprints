<?php

namespace App\Support\Commercial\Reports;

use App\Models\CommercialReportExport;
use App\Models\User;
use App\Support\Commercial\Reports\Exports\CommercialReportExportRegistry;
use App\Support\Commercial\Reports\Exports\CommercialReportExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommercialReportExportService
{
    public function __construct(
        protected CommercialReportExportWriter $writer,
    ) {}

    /**
     * @param  array<string, mixed>  $scopePayload
     */
    public function download(
        Request $request,
        array $scopePayload,
        string $module,
        string $tab,
        string $format,
    ): StreamedResponse {
        $user = $request->user();

        $export = new CommercialReportExport([
            'company_id' => (int) ($scopePayload['company_id'] ?? $user?->company_id),
            'user_id' => (int) $user->id,
            'module' => $module,
            'tab' => $tab,
            'format' => $format,
            'scope_payload' => $scopePayload,
        ]);

        $exporter = CommercialReportExportRegistry::resolve($module);

        return $this->writer->streamDownload(
            export: $export,
            columns: $exporter->columns($export),
            rows: $exporter->rows($export),
            title: $exporter->title($export),
            subtitle: $exporter->subtitle($export),
        );
    }

    public function authorizeView(User $user, CommercialReportExport $export): void
    {
        abort_unless($user->can('commercial.reports.exports.view'), 403);

        $companyId = tenant()->companyId() ?? $user->company_id;
        abort_unless($export->company_id === (int) $companyId, 403);

        if (! $user->can('commercial.reports.exports.download')) {
            abort_unless($export->user_id === $user->id, 403);
        }
    }

    public function authorizeDownload(User $user, CommercialReportExport $export): void
    {
        abort_unless($user->can('commercial.reports.exports.download'), 403);

        $companyId = tenant()->companyId() ?? $user->company_id;
        abort_unless($export->company_id === (int) $companyId, 403);

        if (! $user->can('commercial.reports.exports.view')) {
            abort_unless($export->user_id === $user->id, 403);
        }

        abort_if($export->isExpired(), 410, __('This export has expired.'));
        abort_unless($export->isDownloadable(), 404);
    }
}
