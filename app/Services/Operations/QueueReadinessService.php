<?php

namespace App\Services\Operations;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueReadinessService
{
    /**
     * @return list<string>
     */
    public function requiredQueues(): array
    {
        return [
            'default',
            'emails',
            'notifications',
            'exports',
            'sms',
            'whatsapp',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function diagnostics(): array
    {
        $connection = (string) config('queue.default');
        $queues = $this->requiredQueues();
        $backlog = $this->queueBacklog($queues);
        $failed = $this->failedJobCount();
        $warnings = [];

        if ($connection === 'sync') {
            $warnings[] = __('Queue connection is set to sync — background jobs run inline and will not survive web requests in production.');
        }

        foreach ($backlog as $queue => $count) {
            if ($count >= 100) {
                $warnings[] = __('Queue :queue has :count pending jobs.', ['queue' => $queue, 'count' => $count]);
            }
        }

        if ($failed > 0) {
            $warnings[] = __(':count failed jobs require attention.', ['count' => $failed]);
        }

        return [
            'connection' => $connection,
            'driver_ready' => $connection !== 'sync',
            'queues' => $queues,
            'backlog' => $backlog,
            'failed_jobs' => $failed,
            'warnings' => $warnings,
            'healthy' => $warnings === [] && $connection !== 'sync',
            'worker_commands' => $this->workerCommands(),
            'scheduler_tasks' => $this->schedulerTasks(),
            'maintenance_commands' => $this->maintenanceCommands(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function queueBacklog(array $queues): array
    {
        if (! Schema::hasTable('jobs')) {
            return array_fill_keys($queues, 0);
        }

        $counts = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as total'))
            ->whereIn('queue', $queues)
            ->groupBy('queue')
            ->pluck('total', 'queue')
            ->all();

        $backlog = [];
        foreach ($queues as $queue) {
            $backlog[$queue] = (int) ($counts[$queue] ?? 0);
        }

        return $backlog;
    }

    public function failedJobCount(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        return (int) DB::table('failed_jobs')->count();
    }

    /**
     * @return list<array{label: string, command: string, description: string}>
     */
    public function workerCommands(): array
    {
        $php = PHP_BINARY;

        return [
            [
                'label' => __('Default queue worker'),
                'command' => "{$php} artisan queue:work database --queue=default,notifications,exports,emails,sms,whatsapp --sleep=3 --tries=3 --max-time=3600",
                'description' => __('Processes all platform queues using the database driver.'),
            ],
            [
                'label' => __('Restart workers after deploy'),
                'command' => "{$php} artisan queue:restart",
                'description' => __('Gracefully restart queue workers after code or config changes.'),
            ],
            [
                'label' => __('Retry failed jobs'),
                'command' => "{$php} artisan queue:retry all",
                'description' => __('Re-queue all failed jobs after fixing the underlying issue.'),
            ],
            [
                'label' => __('Scheduler (cron)'),
                'command' => "* * * * * cd ".base_path()." && {$php} artisan schedule:run >> /dev/null 2>&1",
                'description' => __('Required cron entry for scheduled ERP tasks.'),
            ],
        ];
    }

    /**
     * @return list<array{command: string, schedule: string, configured: bool}>
     */
    public function schedulerTasks(): array
    {
        return [
            ['command' => 'commercial:expire-report-exports', 'schedule' => __('Daily'), 'configured' => true],
            ['command' => 'governance:process-escalations', 'schedule' => __('Every 15 minutes'), 'configured' => true],
            ['command' => 'inventory:velocity:snapshot --all-windows', 'schedule' => __('Daily at 02:30'), 'configured' => true],
            ['command' => 'communications:dispatch-scheduled-events', 'schedule' => __('Recommended hourly'), 'configured' => false],
            ['command' => 'communications:dispatch-payment-reminders', 'schedule' => __('Recommended daily'), 'configured' => false],
            ['command' => 'printing-intelligence:generate-profitability-snapshots', 'schedule' => __('Recommended nightly'), 'configured' => false],
        ];
    }

    /**
     * @return list<array{label: string, command: string}>
     */
    public function maintenanceCommands(): array
    {
        return [
            ['label' => __('Inspect queue status'), 'command' => 'php artisan queue:monitor'],
            ['label' => __('List failed jobs'), 'command' => 'php artisan queue:failed'],
            ['label' => __('Flush failed jobs'), 'command' => 'php artisan queue:flush'],
            ['label' => __('Run scheduler once'), 'command' => 'php artisan schedule:run'],
        ];
    }
}
