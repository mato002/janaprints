<?php

namespace App\Console\Commands;

use App\Support\Inventory\InventoryLifecycleInspector;
use Illuminate\Console\Command;

class InspectInventoryLifecycleCommand extends Command
{
    protected $signature = 'inventory:lifecycle:inspect';

    protected $description = 'Inspect inventory lifecycle governance controls (Phase I4.1)';

    public function handle(InventoryLifecycleInspector $inspector): int
    {
        $this->info(__('Inventory Lifecycle Inspection (Phase I4.1)'));
        $this->newLine();

        $report = $inspector->inspect();

        foreach ($report['checks'] as $check) {
            $status = $check['status'] === 'PASS'
                ? '<fg=green>PASS</>'
                : '<fg=red>FAIL</>';

            $this->line(sprintf('[%s] %s', $status, $check['label']));
            $this->line('  '.$check['detail']);
        }

        $this->newLine();
        $this->info(__('Summary: :score', ['score' => $report['score']]));

        return $report['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
