<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Assets\AssetIntelligenceNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScanAssetIntelligenceAlerts extends Command
{
    protected $signature = 'assets:scan-intelligence-alerts {--company= : Limit scan to a company ID}';

    protected $description = 'Scan assets and emit warranty, maintenance, health, and replacement alerts';

    public function handle(AssetIntelligenceNotificationService $scanner): int
    {
        $companyId = $this->option('company');
        $query = Company::query()->where('is_active', true);

        if ($companyId) {
            $query->whereKey((int) $companyId);
        }

        $total = 0;

        $query->orderBy('id')->chunkById(50, function ($companies) use ($scanner, &$total) {
            foreach ($companies as $company) {
                $sent = $scanner->scanCompany((int) $company->id);
                $total += $sent;
                $this->line("Company {$company->id}: {$sent} alert(s)");
            }
        });

        Log::info('Asset intelligence alerts scanned', ['notifications' => $total]);
        $this->info("Emitted {$total} asset intelligence notification(s).");

        return self::SUCCESS;
    }
}
