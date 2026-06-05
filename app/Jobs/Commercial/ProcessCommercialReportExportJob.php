<?php

namespace App\Jobs\Commercial;

use App\Enums\CommercialReportExportStatus;
use App\Jobs\Commercial\Concerns\ManagesCommercialReportExport;
use App\Jobs\PlatformJob;
use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\Exports\CommercialReportExportRegistry;
use App\Support\Commercial\Reports\Exports\CommercialReportExportWriter;
use Throwable;

class ProcessCommercialReportExportJob extends PlatformJob
{
    use ManagesCommercialReportExport;

    public function __construct(
        public int $exportId,
    ) {
        parent::__construct();
        $this->useQueue('exports');
    }

    public function handle(CommercialReportExportWriter $writer): void
    {
        $export = CommercialReportExport::query()->findOrFail($this->exportId);

        if ($export->status !== CommercialReportExportStatus::Queued) {
            return;
        }

        $this->markExportProcessing($this->exportId);

        $exporter = CommercialReportExportRegistry::resolve($export->module);
        $columns = $exporter->columns($export);
        $rows = $exporter->rows($export);

        $result = $writer->write(
            export: $export,
            columns: $columns,
            rows: $rows,
            title: $exporter->title($export),
            subtitle: $exporter->subtitle($export),
        );

        $this->completeExport(
            exportId: $this->exportId,
            result: $result,
            logContext: 'Commercial report export completed',
        );
    }

    public function failed(Throwable $exception): void
    {
        $this->failExport($this->exportId, $exception, 'Commercial report export failed');
    }
}
