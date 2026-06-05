<?php

namespace App\Services\Operations;

use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\SystemHealthStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class SystemHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $kpis = $this->kpis();
        $database = $this->databaseMetrics();
        $queue = $this->queueMetrics();
        $storage = $this->storageMetrics();
        $alerts = $this->buildAlerts($kpis, $database, $queue, $storage);

        $statuses = array_map(
            fn (array $kpi) => $kpi['status'],
            $kpis,
        );

        return [
            'generated_at' => now()->toIso8601String(),
            'generated_at_formatted' => now()->format('M j, Y g:i:s A'),
            'system_status' => SystemHealthStatus::worst(...$statuses),
            'kpis' => $kpis,
            'database' => $database,
            'queue' => $queue,
            'storage' => $storage,
            'alerts' => $alerts,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function kpis(): array
    {
        return [
            'application' => $this->applicationKpi(),
            'database' => $this->databaseKpi(),
            'queue' => $this->queueKpi(),
            'storage' => $this->storageKpi(),
            'memory' => $this->memoryKpi(),
            'cpu' => $this->cpuKpi(),
            'cache' => $this->cacheKpi(),
            'session' => $this->sessionKpi(),
            'mail' => $this->mailKpi(),
            'sms' => $this->smsKpi(),
            'backup' => $this->backupKpi(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function databaseMetrics(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);
        $databaseName = (string) ($config['database'] ?? $connection);
        $connected = false;
        $responseTimeMs = null;
        $tableCount = 0;
        $failedQueries = 0;
        $error = null;

        try {
            $started = microtime(true);
            DB::connection($connection)->select('SELECT 1');
            $responseTimeMs = round((microtime(true) - $started) * 1000, 2);
            $connected = true;
            $tableCount = $this->countTables($connection, $databaseName);
            $failedQueries = $this->countRecentDatabaseFailures();
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        $pendingMigrations = $this->pendingMigrationCount();
        $slowQueries = $this->countSlowQueries();

        $status = SystemHealthStatus::Healthy;

        if (! $connected) {
            $status = SystemHealthStatus::Critical;
        } elseif ($pendingMigrations > 0 || $responseTimeMs !== null && $responseTimeMs > 500) {
            $status = SystemHealthStatus::Warning;
        } elseif ($failedQueries > 0 || $slowQueries > 0) {
            $status = SystemHealthStatus::Warning;
        }

        return [
            'connection' => $connection,
            'database_name' => $databaseName,
            'connected' => $connected,
            'connection_status' => $connected ? __('Connected') : __('Disconnected'),
            'response_time_ms' => $responseTimeMs,
            'response_time_label' => $responseTimeMs !== null ? "{$responseTimeMs} ms" : __('N/A'),
            'table_count' => $tableCount,
            'migration_status' => $pendingMigrations === 0
                ? __('Up to date')
                : __(':count pending', ['count' => $pendingMigrations]),
            'pending_migrations' => $pendingMigrations,
            'failed_queries' => $failedQueries,
            'slow_queries' => $slowQueries,
            'status' => $status,
            'error' => $error,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function queueMetrics(): array
    {
        $driver = (string) config('queue.default', 'sync');
        $pendingJobs = 0;
        $failedJobs = 0;
        $longestWaiting = null;
        $workersRunning = null;

        if (Schema::hasTable('jobs')) {
            $pendingJobs = (int) DB::table('jobs')->count();

            $oldest = DB::table('jobs')->orderBy('created_at')->value('created_at');

            if ($oldest !== null) {
                $longestWaiting = Carbon::createFromTimestamp((int) $oldest)->diffForHumans(now(), true);
            }
        }

        if (Schema::hasTable('failed_jobs')) {
            $failedJobs = (int) DB::table('failed_jobs')->count();
        }

        if ($driver === 'sync') {
            $workersRunning = 0;
        } else {
            $workersRunning = null;
        }

        $status = SystemHealthStatus::Healthy;

        if ($failedJobs > 0) {
            $status = SystemHealthStatus::Critical;
        } elseif ($pendingJobs > 100) {
            $status = SystemHealthStatus::Warning;
        }

        return [
            'driver' => $driver,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'longest_waiting_job' => $longestWaiting ?? __('None'),
            'workers_running' => $workersRunning,
            'workers_label' => $workersRunning === null ? __('Not detected') : (string) $workersRunning,
            'status' => $status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function storageMetrics(): array
    {
        $root = storage_path();
        $total = @disk_total_space($root) ?: 0;
        $free = @disk_free_space($root) ?: 0;
        $used = max(0, $total - $free);
        $usagePercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        $uploadsPath = storage_path('app/public');
        $backupPath = $this->resolveBackupDirectory();
        $uploadsUsage = $this->directorySize($uploadsPath);
        $backupUsage = $backupPath !== null ? $this->directorySize($backupPath) : 0;

        $status = SystemHealthStatus::Healthy;

        if ($usagePercent >= 95) {
            $status = SystemHealthStatus::Critical;
        } elseif ($usagePercent >= 85) {
            $status = SystemHealthStatus::Warning;
        }

        return [
            'used_bytes' => $used,
            'used_label' => $this->formatBytes($used),
            'free_bytes' => $free,
            'free_label' => $this->formatBytes($free),
            'total_bytes' => $total,
            'usage_percent' => $usagePercent,
            'uploads_bytes' => $uploadsUsage,
            'uploads_label' => $this->formatBytes($uploadsUsage),
            'backup_bytes' => $backupUsage,
            'backup_label' => $this->formatBytes($backupUsage),
            'backup_path' => $backupPath,
            'status' => $status,
        ];
    }

    public function refreshOperationalCaches(): void
    {
        Cache::forget('system_health.snapshot');

        try {
            Artisan::call('cache:clear');
        } catch (Throwable) {
            // Non-fatal during health refresh.
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function buildAlerts(array $kpis, array $database, array $queue, array $storage): array
    {
        $alerts = [];

        if ($queue['failed_jobs'] > 0) {
            $alerts[] = [
                'type' => 'queue',
                'severity' => SystemHealthStatus::Critical,
                'title' => __('Failed queue jobs detected'),
                'message' => __(':count jobs require attention in the failed queue.', ['count' => $queue['failed_jobs']]),
            ];
        }

        if ($storage['usage_percent'] >= 85) {
            $alerts[] = [
                'type' => 'storage',
                'severity' => $storage['status'],
                'title' => __('Storage capacity warning'),
                'message' => __('Disk usage is at :percent%.', ['percent' => $storage['usage_percent']]),
            ];
        }

        if (! $database['connected']) {
            $alerts[] = [
                'type' => 'database',
                'severity' => SystemHealthStatus::Critical,
                'title' => __('Database connection failure'),
                'message' => $database['error'] ?? __('Unable to connect to the database.'),
            ];
        } elseif ($database['pending_migrations'] > 0) {
            $alerts[] = [
                'type' => 'database',
                'severity' => SystemHealthStatus::Warning,
                'title' => __('Pending database migrations'),
                'message' => __(':count migrations have not been applied.', ['count' => $database['pending_migrations']]),
            ];
        }

        if (($kpis['backup']['status'] ?? SystemHealthStatus::Healthy)->rank() >= SystemHealthStatus::Warning->rank()) {
            $alerts[] = [
                'type' => 'backup',
                'severity' => $kpis['backup']['status'],
                'title' => __('Backup attention required'),
                'message' => $kpis['backup']['detail'] ?? __('Review backup schedules and retention.'),
            ];
        }

        if ($queue['pending_jobs'] > 100 && $queue['failed_jobs'] === 0) {
            $alerts[] = [
                'type' => 'queue',
                'severity' => SystemHealthStatus::Warning,
                'title' => __('Queue backlog building'),
                'message' => __(':count jobs are waiting to be processed.', ['count' => $queue['pending_jobs']]),
            ];
        }

        return $alerts;
    }

    /**
     * @return array<string, mixed>
     */
    protected function applicationKpi(): array
    {
        $debug = (bool) config('app.debug');
        $environment = (string) config('app.env', 'production');

        $status = SystemHealthStatus::Healthy;
        $detail = __('Running :env on PHP :version', [
            'env' => $environment,
            'version' => PHP_VERSION,
        ]);

        if ($debug && $environment === 'production') {
            $status = SystemHealthStatus::Critical;
            $detail = __('Debug mode is enabled in production.');
        } elseif ($debug) {
            $status = SystemHealthStatus::Warning;
            $detail = __('Debug mode is enabled.');
        }

        return [
            'label' => __('Application Status'),
            'status' => $status,
            'value' => $status->label(),
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function databaseKpi(): array
    {
        $metrics = $this->databaseMetrics();

        return [
            'label' => __('Database Status'),
            'status' => $metrics['status'],
            'value' => $metrics['connection_status'],
            'detail' => $metrics['connected']
                ? __('Response :time', ['time' => $metrics['response_time_label']])
                : ($metrics['error'] ?? __('Connection failed')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function queueKpi(): array
    {
        $metrics = $this->queueMetrics();

        return [
            'label' => __('Queue Status'),
            'status' => $metrics['status'],
            'value' => $metrics['failed_jobs'] > 0
                ? __(':count failed', ['count' => $metrics['failed_jobs']])
                : __(':count pending', ['count' => $metrics['pending_jobs']]),
            'detail' => __('Driver: :driver', ['driver' => $metrics['driver']]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function storageKpi(): array
    {
        $metrics = $this->storageMetrics();

        return [
            'label' => __('Storage Usage'),
            'status' => $metrics['status'],
            'value' => $metrics['usage_percent'].'%',
            'detail' => __(':used used, :free free', [
                'used' => $metrics['used_label'],
                'free' => $metrics['free_label'],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function memoryKpi(): array
    {
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit((string) ini_get('memory_limit'));
        $percent = $limit > 0 ? round(($used / $limit) * 100, 1) : 0;

        $status = SystemHealthStatus::Healthy;

        if ($percent >= 90) {
            $status = SystemHealthStatus::Critical;
        } elseif ($percent >= 75) {
            $status = SystemHealthStatus::Warning;
        }

        return [
            'label' => __('Memory Usage'),
            'status' => $status,
            'value' => $this->formatBytes($used),
            'detail' => __('Peak :peak of :limit', [
                'peak' => $this->formatBytes($peak),
                'limit' => $limit > 0 ? $this->formatBytes($limit) : ini_get('memory_limit'),
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function cpuKpi(): array
    {
        $status = SystemHealthStatus::Healthy;
        $value = __('N/A');
        $detail = __('Load average not available on this host.');

        if (function_exists('sys_getloadavg')) {
            $load = \sys_getloadavg();

            if (is_array($load) && $load !== []) {
                $oneMinute = round($load[0], 2);
                $value = (string) $oneMinute;
                $detail = __('1m: :one | 5m: :five | 15m: :fifteen', [
                    'one' => round($load[0], 2),
                    'five' => round($load[1] ?? 0, 2),
                    'fifteen' => round($load[2] ?? 0, 2),
                ]);

                if ($oneMinute >= 4) {
                    $status = SystemHealthStatus::Critical;
                } elseif ($oneMinute >= 2) {
                    $status = SystemHealthStatus::Warning;
                }
            }
        }

        return [
            'label' => __('CPU Usage'),
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function cacheKpi(): array
    {
        $key = 'system_health_probe_'.uniqid('', true);
        $status = SystemHealthStatus::Healthy;
        $value = __('Operational');
        $detail = __('Driver: :driver', ['driver' => config('cache.default')]);

        try {
            Cache::put($key, 'ok', 10);
            $probe = Cache::get($key);
            Cache::forget($key);

            if ($probe !== 'ok') {
                $status = SystemHealthStatus::Critical;
                $value = __('Failed');
                $detail = __('Cache read/write probe failed.');
            }
        } catch (Throwable $exception) {
            $status = SystemHealthStatus::Critical;
            $value = __('Failed');
            $detail = $exception->getMessage();
        }

        return [
            'label' => __('Cache Status'),
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sessionKpi(): array
    {
        $driver = (string) config('session.driver', 'file');
        $activeSessions = null;
        $status = SystemHealthStatus::Healthy;
        $value = ucfirst($driver);
        $detail = __('Session driver is configured.');

        if ($driver === 'database' && Schema::hasTable('sessions')) {
            $activeSessions = (int) DB::table('sessions')->count();
            $value = (string) $activeSessions;
            $detail = __('Active sessions stored in database.');
        } elseif ($driver === 'file') {
            $sessionPath = storage_path('framework/sessions');
            $activeSessions = count(glob($sessionPath.'/*') ?: []);
            $value = (string) $activeSessions;
            $detail = __('Session files on disk.');
        }

        return [
            'label' => __('Session Status'),
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mailKpi(): array
    {
        $mailer = (string) config('mail.default', 'log');
        $status = SystemHealthStatus::Healthy;
        $value = ucfirst($mailer);
        $detail = __('Mail transport configured.');
        $recentFailures = $this->countRecentCommunicationFailures(CommunicationLogChannel::Email);

        if (in_array($mailer, ['log', 'array'], true) && config('app.env') === 'production') {
            $status = SystemHealthStatus::Warning;
            $detail = __('Non-production mailer in production environment.');
        }

        if ($recentFailures > 0) {
            $status = SystemHealthStatus::worst($status, SystemHealthStatus::Warning);
            $detail = __(':count recent delivery failures.', ['count' => $recentFailures]);
        }

        return [
            'label' => __('Mail Status'),
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function smsKpi(): array
    {
        $status = SystemHealthStatus::Healthy;
        $value = __('Configured');
        $detail = __('SMS channel available.');
        $recentFailures = $this->countRecentCommunicationFailures(CommunicationLogChannel::Sms);

        if (! Schema::hasTable('communication_logs')) {
            $value = __('Not monitored');
            $detail = __('Communication logs are not available.');
        } elseif ($recentFailures > 0) {
            $status = SystemHealthStatus::Warning;
            $value = __(':count failures', ['count' => $recentFailures]);
            $detail = __('Recent SMS delivery failures detected.');
        }

        return [
            'label' => __('SMS Status'),
            'status' => $status,
            'value' => $value,
            'detail' => $detail,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function backupKpi(): array
    {
        $latest = $this->latestBackup();

        if ($latest === null) {
            return [
                'label' => __('Last Backup'),
                'status' => SystemHealthStatus::Warning,
                'value' => __('None found'),
                'detail' => __('No backup artifacts detected on disk.'),
            ];
        }

        $ageHours = $latest['timestamp']->diffInHours(now());
        $status = SystemHealthStatus::Healthy;

        if ($ageHours >= 168) {
            $status = SystemHealthStatus::Critical;
        } elseif ($ageHours >= 48) {
            $status = SystemHealthStatus::Warning;
        }

        return [
            'label' => __('Last Backup'),
            'status' => $status,
            'value' => $latest['timestamp']->diffForHumans(),
            'detail' => $latest['path'],
        ];
    }

    protected function countTables(string $connection, string $databaseName): int
    {
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $result = DB::connection($connection)->select(
                "SELECT COUNT(*) AS aggregate FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
            );

            return (int) ($result[0]->aggregate ?? 0);
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::connection($connection)->select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.tables WHERE table_schema = ?',
                [$databaseName],
            );

            return (int) ($result[0]->aggregate ?? 0);
        }

        if ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                "SELECT COUNT(*) AS aggregate FROM information_schema.tables WHERE table_schema = 'public'",
            );

            return (int) ($result[0]->aggregate ?? 0);
        }

        return 0;
    }

    protected function pendingMigrationCount(): int
    {
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();

            return count(array_diff(array_keys($files), $ran));
        } catch (Throwable) {
            return 0;
        }
    }

    protected function countRecentDatabaseFailures(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        return (int) DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subDay())
            ->count();
    }

    protected function countSlowQueries(): int
    {
        return 0;
    }

    protected function countRecentCommunicationFailures(CommunicationLogChannel $channel): int
    {
        if (! Schema::hasTable('communication_logs')) {
            return 0;
        }

        return (int) DB::table('communication_logs')
            ->where('channel', $channel->value)
            ->where('status', CommunicationLogStatus::Failed->value)
            ->where('failed_at', '>=', now()->subDay())
            ->count();
    }

    /**
     * @return array{timestamp: Carbon, path: string}|null
     */
    protected function latestBackup(): ?array
    {
        $directories = [
            storage_path('app/backups'),
            storage_path('backups'),
            base_path('storage/backups'),
        ];

        $latestTimestamp = null;
        $latestPath = null;

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $modified = Carbon::createFromTimestamp($file->getMTime());

                if ($latestTimestamp === null || $modified->greaterThan($latestTimestamp)) {
                    $latestTimestamp = $modified;
                    $latestPath = $file->getPathname();
                }
            }
        }

        if ($latestTimestamp === null) {
            return null;
        }

        return [
            'timestamp' => $latestTimestamp,
            'path' => $latestPath,
        ];
    }

    protected function resolveBackupDirectory(): ?string
    {
        foreach ([storage_path('app/backups'), storage_path('backups')] as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function directorySize(string $path): int
    {
        if (! is_dir($path)) {
            return 0;
        }

        $size = 0;

        try {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (Throwable) {
            return 0;
        }

        return $size;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }

    protected function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return 0;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
