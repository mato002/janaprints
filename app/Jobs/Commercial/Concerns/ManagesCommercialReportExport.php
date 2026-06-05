<?php

namespace App\Jobs\Commercial\Concerns;

use App\Enums\CommercialReportExportStatus;
use App\Models\CommercialReportExport;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ManagesCommercialReportExport
{
    protected function markExportProcessing(int $exportId): void
    {
        CommercialReportExport::query()
            ->whereKey($exportId)
            ->update(['status' => CommercialReportExportStatus::Processing]);
    }

    /**
     * @param  array{path: string, filename: string, mime_type: string, row_count: int}  $result
     */
    protected function completeExport(int $exportId, array $result, string $logContext): void
    {
        $ttlDays = (int) config('platform.commercial_reports.export_ttl_days', 7);

        CommercialReportExport::query()->whereKey($exportId)->update([
            'status' => CommercialReportExportStatus::Completed,
            'storage_path' => $result['path'],
            'filename' => $result['filename'],
            'mime_type' => $result['mime_type'],
            'row_count' => $result['row_count'],
            'completed_at' => now(),
            'expires_at' => now()->addDays($ttlDays),
            'error_message' => null,
        ]);

        Log::info($logContext, [
            'export_id' => $exportId,
            'path' => $result['path'],
            'row_count' => $result['row_count'],
        ]);
    }

    protected function failExport(int $exportId, Throwable $exception, string $logContext): void
    {
        CommercialReportExport::query()->whereKey($exportId)->update([
            'status' => CommercialReportExportStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);

        Log::error($logContext, [
            'export_id' => $exportId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
