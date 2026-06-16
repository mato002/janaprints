<?php

namespace App\Console\Commands;

use App\Services\EmailIdentity\EmployeeRoleReconciliationService;
use Illuminate\Console\Command;

class ReconcileEmployeeRolesCommand extends Command
{
    protected $signature = 'employees:reconcile-roles
                            {--company= : Limit to a company id}
                            {--dry-run : Report mismatches without updating roles}';

    protected $description = 'Align employee user roles with activation role, job title, or department mappings';

    public function handle(EmployeeRoleReconciliationService $reconciliation): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $dryRun = (bool) $this->option('dry-run');

        $summary = $reconciliation->reconcile($companyId, dryRun: $dryRun);

        $this->info(__('Checked :count employee(s).', ['count' => $summary['checked']]));
        $this->line(__('OK: :ok | Skipped: :skipped | Fixed: :fixed', [
            'ok' => $summary['ok'],
            'skipped' => $summary['skipped'],
            'fixed' => $summary['fixed'],
        ]));

        foreach ($summary['results'] as $result) {
            if ($result['status'] === 'ok') {
                continue;
            }

            $this->line(sprintf(
                '%s %s <%s> — %s%s',
                strtoupper($result['status']),
                $result['employee_number'],
                $result['email'] ?? '—',
                $result['message'],
                $result['expected'] ? ' ['.$result['expected'].']' : '',
            ));
        }

        if ($dryRun && collect($summary['results'])->contains(fn ($row) => $row['status'] === 'mismatch')) {
            $this->warn(__('Run without --dry-run to apply role fixes.'));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
