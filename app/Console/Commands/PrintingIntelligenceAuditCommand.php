<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PrintingIntelligence\PrintingIntelligenceAuditService;
use Illuminate\Console\Command;

class PrintingIntelligenceAuditCommand extends Command
{
    protected $signature = 'printing:intelligence:audit {--company= : Company ID to audit}';

    protected $description = 'Run PI9.7 production readiness integrity checks';

    public function handle(PrintingIntelligenceAuditService $audit): int
    {
        $companyId = $this->option('company')
            ? (int) $this->option('company')
            : (int) (Company::query()->value('id') ?? 0);

        $this->info(__('Printing Intelligence Integrity Audit (PI9.7)'));
        $this->newLine();

        $results = $audit->run($companyId);
        $rows = [];
        $failures = 0;

        foreach ($results as $check => $result) {
            $rows[] = [str_replace('_', ' ', ucfirst($check)), $result['status'], $result['detail']];
            if ($result['status'] === 'FAIL') {
                $failures++;
            }
        }

        $this->table([__('Check'), __('Status'), __('Detail')], $rows);
        $this->newLine();
        $this->line(__('Summary: :pass/:total PASS', [
            'pass' => count($results) - $failures,
            'total' => count($results),
        ]));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
