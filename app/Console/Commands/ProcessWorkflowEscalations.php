<?php

namespace App\Console\Commands;

use App\Support\Governance\EscalationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessWorkflowEscalations extends Command
{
    protected $signature = 'governance:process-escalations {--company= : Limit processing to a company ID}';

    protected $description = 'Evaluate pending approval steps and apply escalation rules';

    public function handle(EscalationEngine $engine): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        $stats = $engine->processPendingSteps($companyId);

        Log::info('Workflow escalations processed', $stats);
        $this->info(sprintf(
            'Processed escalations: %d reminder(s), %d auto-escalation(s), %d skipped.',
            $stats['reminders'],
            $stats['escalations'],
            $stats['skipped'],
        ));

        return self::SUCCESS;
    }
}
