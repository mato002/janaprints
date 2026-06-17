<?php

namespace App\Console\Commands;

use App\Support\Hr\EmployeeLifecycleService;
use Illuminate\Console\Command;

class PurgeDemoEmployeesCommand extends Command
{
    protected $signature = 'employees:purge-demo
                            {--company= : Limit to a company id}
                            {--dry-run : List records that would be removed without deleting}';

    protected $description = 'Permanently remove inactive demo employees and their login accounts';

    public function handle(EmployeeLifecycleService $lifecycle): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $dryRun = (bool) $this->option('dry-run');

        $candidates = $lifecycle->findDemoAndInactiveEmployees($companyId);

        if ($candidates['count'] === 0) {
            $this->info(__('No demo or inactive employees matched the purge criteria.'));

            return self::SUCCESS;
        }

        $this->warn(__('Matched :count employee record(s):', ['count' => $candidates['count']]));

        foreach ($candidates['employees'] as $employee) {
            $this->line(sprintf(
                '  - %s | %s | %s | active=%s',
                $employee->employee_number,
                $employee->full_name,
                $employee->email ?? '—',
                $employee->is_active ? 'yes' : 'no',
            ));
        }

        if ($dryRun) {
            $this->comment(__('Dry run only. Re-run without --dry-run to delete these records.'));

            return self::SUCCESS;
        }

        if (! $this->confirm(__('Delete these employees and linked demo login accounts permanently?'), true)) {
            $this->comment(__('Aborted.'));

            return self::SUCCESS;
        }

        $results = $lifecycle->purgeDemoAndInactiveEmployees($companyId);
        $orphans = $lifecycle->purgeOrphanDemoStaffUsers($companyId);

        $this->info(__('Removed :count employee record(s).', ['count' => count($results)]));

        foreach ($results as $result) {
            $this->line(sprintf(
                '  ✓ %s (%s)',
                $result['employee_number'],
                implode(', ', $result['user_emails']) ?: __('no linked users'),
            ));
        }

        if ($orphans > 0) {
            $this->info(__('Removed :count orphan demo login account(s).', ['count' => $orphans]));
        }

        return self::SUCCESS;
    }
}
