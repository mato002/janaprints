<?php

namespace App\Services\Operations;

use App\Enums\BackgroundJobStatus;
use App\Enums\BackgroundJobType;
use App\Enums\CommercialReportExportStatus;
use App\Models\CommercialReportExport;
use App\Models\Operations\BackgroundJobCancellation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class BackgroundJobMonitorService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function allFiltered(array $filters = []): Collection
    {
        return $this->collectRows($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $rows = $this->collectRows($filters);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * @return array<string, int>
     */
    public function summaryMetrics(): array
    {
        $rows = $this->collectRows([]);

        return [
            'total' => $rows->count(),
            'pending' => $rows->where('status', BackgroundJobStatus::Pending)->count(),
            'processing' => $rows->where('status', BackgroundJobStatus::Processing)->count(),
            'failed' => $rows->where('status', BackgroundJobStatus::Failed)->count(),
            'completed' => $rows->where('status', BackgroundJobStatus::Completed)->count(),
            'cancelled' => $rows->where('status', BackgroundJobStatus::Cancelled)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $reference): array
    {
        $row = $this->collectRows([])->firstWhere('reference', $reference);

        if ($row === null) {
            throw (new ModelNotFoundException)->setModel('background_job', [$reference]);
        }

        return $row;
    }

    public function retry(string $reference): void
    {
        if (! str_starts_with($reference, 'failed:')) {
            throw new \InvalidArgumentException(__('Only failed jobs can be retried.'));
        }

        $uuid = substr($reference, strlen('failed:'));

        Artisan::call('queue:retry', ['id' => $uuid]);
    }

    public function retryAllFailed(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        $count = (int) DB::table('failed_jobs')->count();

        if ($count === 0) {
            return 0;
        }

        Artisan::call('queue:retry', ['id' => 'all']);

        return $count;
    }

    public function cancel(string $reference, User $user): void
    {
        if (! str_starts_with($reference, 'pending:')) {
            throw new \InvalidArgumentException(__('Only pending jobs can be cancelled.'));
        }

        $jobId = (int) substr($reference, strlen('pending:'));

        $job = DB::table('jobs')->where('id', $jobId)->first();

        if ($job === null) {
            throw (new ModelNotFoundException)->setModel('jobs', [$jobId]);
        }

        $parsed = $this->parsePayload((string) $job->payload, (int) $job->attempts);
        $type = $this->resolveType((string) $job->queue, $parsed['display_name']);

        BackgroundJobCancellation::query()->create([
            'reference' => 'cancelled:'.($parsed['uuid'] ?? (string) Str::uuid()),
            'queue_job_id' => $jobId,
            'queue' => $job->queue,
            'job_class' => $parsed['display_name'],
            'job_type' => $type->value,
            'payload' => $job->payload,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
        ]);

        DB::table('jobs')->where('id', $jobId)->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    protected function collectRows(array $filters): Collection
    {
        $rows = collect()
            ->merge($this->queueJobRows())
            ->merge($this->failedJobRows())
            ->merge($this->cancelledJobRows())
            ->merge($this->domainExportRows());

        $type = (string) ($filters['type'] ?? 'all');
        $status = (string) ($filters['status'] ?? 'all');
        $queue = (string) ($filters['queue'] ?? 'all');
        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($type !== 'all') {
            $rows = $rows->filter(fn (array $row) => $row['type']->value === $type);
        }

        if ($status !== 'all') {
            $rows = $rows->filter(fn (array $row) => $row['status']->value === $status);
        }

        if ($queue !== 'all') {
            $rows = $rows->filter(fn (array $row) => $row['queue'] === $queue);
        }

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search) {
                $blob = Str::lower(implode(' ', array_filter([
                    $row['job_id'],
                    $row['queue'],
                    $row['type']->value,
                    $row['status']->value,
                    $row['job_class'],
                    $row['error'] ?? '',
                ])));

                return str_contains($blob, $search);
            });
        }

        return $rows
            ->sortByDesc(fn (array $row) => $row['sort_at']?->timestamp ?? 0)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function queueJobRows(): Collection
    {
        if (! Schema::hasTable('jobs')) {
            return collect();
        }

        return DB::table('jobs')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (object $job) {
                $parsed = $this->parsePayload((string) $job->payload, (int) $job->attempts);
                $startedAt = $job->reserved_at
                    ? Carbon::createFromTimestamp((int) $job->reserved_at)
                    : Carbon::createFromTimestamp((int) $job->created_at);
                $status = $job->reserved_at
                    ? BackgroundJobStatus::Processing
                    : BackgroundJobStatus::Pending;

                return $this->normalizeRow(
                    reference: 'pending:'.$job->id,
                    jobId: $parsed['uuid'] ?? (string) $job->id,
                    queue: (string) $job->queue,
                    type: $this->resolveType((string) $job->queue, $parsed['display_name']),
                    status: $status,
                    startedAt: $startedAt,
                    completedAt: null,
                    attempts: (int) $job->attempts,
                    error: null,
                    errorFull: null,
                    jobClass: $parsed['display_name'],
                    canRetry: false,
                    canCancel: true,
                    sortAt: $startedAt,
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function failedJobRows(): Collection
    {
        if (! Schema::hasTable('failed_jobs')) {
            return collect();
        }

        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->get()
            ->map(function (object $job) {
                $parsed = $this->parsePayload((string) $job->payload, 1);
                $failedAt = Carbon::parse($job->failed_at);
                $errorFull = (string) $job->exception;

                return $this->normalizeRow(
                    reference: 'failed:'.$job->uuid,
                    jobId: (string) $job->uuid,
                    queue: (string) $job->queue,
                    type: $this->resolveType((string) $job->queue, $parsed['display_name']),
                    status: BackgroundJobStatus::Failed,
                    startedAt: $failedAt,
                    completedAt: $failedAt,
                    attempts: (int) ($parsed['attempts'] ?? 1),
                    error: Str::limit($this->extractExceptionMessage($errorFull), 120),
                    errorFull: $errorFull,
                    jobClass: $parsed['display_name'],
                    canRetry: true,
                    canCancel: false,
                    sortAt: $failedAt,
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function cancelledJobRows(): Collection
    {
        if (! Schema::hasTable('background_job_cancellations')) {
            return collect();
        }

        return BackgroundJobCancellation::query()
            ->orderByDesc('cancelled_at')
            ->get()
            ->map(function (BackgroundJobCancellation $cancellation) {
                $cancelledAt = $cancellation->cancelled_at ?? now();

                return $this->normalizeRow(
                    reference: $cancellation->reference,
                    jobId: (string) ($cancellation->queue_job_id ?? $cancellation->reference),
                    queue: $cancellation->queue,
                    type: BackgroundJobType::from($cancellation->job_type),
                    status: BackgroundJobStatus::Cancelled,
                    startedAt: $cancelledAt,
                    completedAt: $cancelledAt,
                    attempts: 0,
                    error: null,
                    errorFull: null,
                    jobClass: $cancellation->job_class,
                    canRetry: false,
                    canCancel: false,
                    sortAt: $cancelledAt,
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function domainExportRows(): Collection
    {
        if (! Schema::hasTable('commercial_report_exports')) {
            return collect();
        }

        return CommercialReportExport::query()
            ->whereIn('status', [
                CommercialReportExportStatus::Completed,
                CommercialReportExportStatus::Failed,
                CommercialReportExportStatus::Expired,
            ])
            ->where('queued_at', '>=', now()->subDays(7))
            ->orderByDesc('queued_at')
            ->get()
            ->map(function (CommercialReportExport $export) {
                $status = match ($export->status) {
                    CommercialReportExportStatus::Failed => BackgroundJobStatus::Failed,
                    default => BackgroundJobStatus::Completed,
                };
                $startedAt = $export->queued_at ?? $export->created_at;
                $completedAt = $export->completed_at ?? $export->updated_at;
                $type = in_array($export->format, ['pdf', 'csv', 'excel'], true)
                    ? BackgroundJobType::Export
                    : BackgroundJobType::Report;

                return $this->normalizeRow(
                    reference: 'export:'.$export->id,
                    jobId: 'EXP-'.$export->id,
                    queue: config('platform.queues.exports', 'exports'),
                    type: $type,
                    status: $status,
                    startedAt: $startedAt,
                    completedAt: $completedAt,
                    attempts: 1,
                    error: $export->error_message ? Str::limit($export->error_message, 120) : null,
                    errorFull: $export->error_message,
                    jobClass: 'ProcessCommercialReportExportJob',
                    canRetry: false,
                    canCancel: false,
                    sortAt: $completedAt ?? $startedAt,
                );
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeRow(
        string $reference,
        string $jobId,
        string $queue,
        BackgroundJobType $type,
        BackgroundJobStatus $status,
        ?Carbon $startedAt,
        ?Carbon $completedAt,
        int $attempts,
        ?string $error,
        ?string $errorFull,
        string $jobClass,
        bool $canRetry,
        bool $canCancel,
        ?Carbon $sortAt,
    ): array {
        return [
            'reference' => $reference,
            'job_id' => $jobId,
            'queue' => $queue,
            'type' => $type,
            'status' => $status,
            'started_at' => $startedAt,
            'started_label' => $startedAt?->format('M j, Y g:i A') ?? '—',
            'completed_at' => $completedAt,
            'completed_label' => $completedAt?->format('M j, Y g:i A') ?? '—',
            'duration_label' => $this->formatDuration($startedAt, $completedAt),
            'attempts' => $attempts,
            'error' => $error,
            'error_full' => $errorFull,
            'job_class' => $jobClass,
            'can_retry' => $canRetry,
            'can_cancel' => $canCancel,
            'sort_at' => $sortAt,
        ];
    }

    /**
     * @return array{display_name: string, uuid: ?string, attempts: int}
     */
    protected function parsePayload(string $payload, int $fallbackAttempts = 0): array
    {
        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [
                'display_name' => __('Unknown Job'),
                'uuid' => null,
                'attempts' => $fallbackAttempts,
            ];
        }

        $displayName = $decoded['displayName']
            ?? $decoded['data']['commandName']
            ?? class_basename($decoded['job'] ?? 'Unknown');

        if (is_string($displayName) && str_contains($displayName, '\\')) {
            $displayName = class_basename($displayName);
        }

        return [
            'display_name' => is_string($displayName) ? $displayName : __('Unknown Job'),
            'uuid' => is_string($decoded['uuid'] ?? null) ? $decoded['uuid'] : null,
            'attempts' => (int) ($decoded['attempts'] ?? $fallbackAttempts),
        ];
    }

    protected function resolveType(string $queue, string $className): BackgroundJobType
    {
        $normalizedClass = Str::lower($className);

        if (str_contains($normalizedClass, 'sms')) {
            return BackgroundJobType::Sms;
        }

        if (str_contains($normalizedClass, 'email') || str_contains($normalizedClass, 'mail')) {
            return BackgroundJobType::Email;
        }

        if (str_contains($normalizedClass, 'notification')) {
            return BackgroundJobType::Notification;
        }

        if (str_contains($normalizedClass, 'import')) {
            return BackgroundJobType::Import;
        }

        if (str_contains($normalizedClass, 'export') || str_contains($normalizedClass, 'report')) {
            return str_contains($normalizedClass, 'export')
                ? BackgroundJobType::Export
                : BackgroundJobType::Report;
        }

        if (str_contains($normalizedClass, 'accounting') || str_contains($normalizedClass, 'journal') || str_contains($normalizedClass, 'posting')) {
            return BackgroundJobType::Accounting;
        }

        $queueMap = [
            config('platform.queues.emails', 'emails') => BackgroundJobType::Email,
            config('platform.queues.sms', 'sms') => BackgroundJobType::Sms,
            config('platform.queues.notifications', 'notifications') => BackgroundJobType::Notification,
            config('platform.queues.reports', 'reports') => BackgroundJobType::Report,
            config('platform.queues.exports', 'exports') => BackgroundJobType::Export,
            config('platform.queues.imports', 'imports') => BackgroundJobType::Import,
            'accounting' => BackgroundJobType::Accounting,
        ];

        return $queueMap[$queue] ?? BackgroundJobType::General;
    }

    protected function extractExceptionMessage(string $exception): string
    {
        if (preg_match('/^([^:\n]+Exception):\s*(.+)$/m', $exception, $matches)) {
            return trim($matches[2]);
        }

        return Str::limit(trim(Str::before($exception, "\n")), 120);
    }

    protected function formatDuration(?Carbon $startedAt, ?Carbon $completedAt): string
    {
        if ($startedAt === null) {
            return '—';
        }

        $end = $completedAt ?? now();
        $seconds = max(0, $startedAt->diffInSeconds($end));

        if ($seconds < 60) {
            return $seconds.'s';
        }

        if ($seconds < 3600) {
            return intdiv($seconds, 60).'m '.($seconds % 60).'s';
        }

        return intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
    }

    /**
     * @return array<string, string>
     */
    public function typeOptions(): array
    {
        $options = ['all' => __('All types')];

        foreach (BackgroundJobType::cases() as $type) {
            if ($type === BackgroundJobType::General) {
                continue;
            }

            $options[$type->value] = $type->label();
        }

        $options[BackgroundJobType::General->value] = BackgroundJobType::General->label();

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        $options = ['all' => __('All statuses')];

        foreach (BackgroundJobStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function queueOptions(): array
    {
        $options = ['all' => __('All queues')];
        $queues = array_unique(array_values(config('platform.queues', [])));

        sort($queues);

        foreach ($queues as $queue) {
            $options[$queue] = $queue;
        }

        $options['accounting'] = 'accounting';

        return $options;
    }
}
