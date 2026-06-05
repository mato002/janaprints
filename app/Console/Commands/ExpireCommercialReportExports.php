<?php

namespace App\Console\Commands;

use App\Enums\CommercialReportExportStatus;
use App\Models\CommercialReportExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExpireCommercialReportExports extends Command
{
    protected $signature = 'commercial:expire-report-exports';

    protected $description = 'Mark expired commercial report exports and remove stored files';

    public function handle(): int
    {
        $expired = 0;

        CommercialReportExport::query()
            ->where('status', CommercialReportExportStatus::Completed)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($exports) use (&$expired) {
                foreach ($exports as $export) {
                    if ($export->storage_path && Storage::disk('local')->exists($export->storage_path)) {
                        Storage::disk('local')->delete($export->storage_path);
                    }

                    $export->update([
                        'status' => CommercialReportExportStatus::Expired,
                        'storage_path' => null,
                    ]);

                    $expired++;
                }
            });

        Log::info('Commercial report exports expired', ['count' => $expired]);
        $this->info("Expired {$expired} commercial report export(s).");

        return self::SUCCESS;
    }
}
