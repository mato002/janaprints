<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Inventory\InventoryVelocityService;
use Illuminate\Console\Command;

class InventoryVelocitySnapshotCommand extends Command
{
    protected $signature = 'inventory:velocity:snapshot
                            {--company= : Company ID to process}
                            {--window=30 : Movement window in days}
                            {--all-windows : Generate snapshots for all configured windows}
                            {--warehouse= : Limit to a single warehouse}
                            {--dry-run : Calculate without persisting snapshots}';

    protected $description = 'Generate inventory velocity snapshots from movement ledger (Phase I6)';

    public function handle(InventoryVelocityService $service): int
    {
        $companyId = $this->option('company');
        $dryRun = (bool) $this->option('dry-run');
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;

        $windows = $this->option('all-windows')
            ? config('inventory_intelligence.windows', [7, 30, 90])
            : [(int) $this->option('window')];

        $companies = $companyId
            ? Company::query()->whereKey((int) $companyId)->get()
            : Company::query()->where('is_active', true)->get();

        if ($companies->isEmpty()) {
            $this->error(__('No companies found to process.'));

            return self::FAILURE;
        }

        $this->info(__('Inventory velocity snapshot generation (Phase I6)'));
        if ($dryRun) {
            $this->warn(__('Dry run — snapshots will not be persisted.'));
        }

        $totals = [
            'items_processed' => 0,
            'critical_risks' => 0,
            'dead_stock_count' => 0,
            'fast_movers' => 0,
            'duration_ms' => 0.0,
        ];

        foreach ($companies as $company) {
            $this->line(__('Processing company :name (#:id)', ['name' => $company->name, 'id' => $company->id]));

            $summary = $service->generateSnapshots(
                companyId: (int) $company->id,
                branchId: null,
                windows: $windows,
                warehouseId: $warehouseId,
                dryRun: $dryRun,
                syncAlerts: ! $dryRun,
            );

            foreach ($totals as $key => $value) {
                if ($key === 'duration_ms') {
                    $totals[$key] += $summary[$key];
                } else {
                    $totals[$key] += $summary[$key];
                }
            }
        }

        $this->newLine();
        $this->table(
            [__('Metric'), __('Value')],
            [
                [__('Items processed'), $totals['items_processed']],
                [__('Critical risks'), $totals['critical_risks']],
                [__('Dead stock count'), $totals['dead_stock_count']],
                [__('Fast movers'), $totals['fast_movers']],
                [__('Windows'), implode(', ', $windows).' '.__('days')],
                [__('Duration (ms)'), $totals['duration_ms']],
            ],
        );

        return self::SUCCESS;
    }
}
