<?php

namespace App\Support\Export;

use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Models\ActivityLog;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWebhook;
use App\Models\Operations\SystemBackup;
use App\Models\UserSessionRecord;
use App\Operations\BackgroundJobsCenter;
use App\Operations\BackupsCenter;
use App\Services\Operations\BackgroundJobMonitorService;
use App\Services\Operations\BackupManagementService;
use App\Services\Security\UserSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdministrationListingExporter
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    /** @var list<string> */
    protected array $listings = [
        'email-providers',
        'sms-providers',
        'webhooks',
        'user-sessions',
        'activity-logs',
        'background-jobs',
        'backups',
    ];

    public function __construct(
        protected UserSessionService $userSessions,
        protected BackgroundJobMonitorService $backgroundJobs,
        protected BackupManagementService $backups,
    ) {}

    public function download(
        string $listing,
        string $format,
        TabularExportWriter $writer,
        Request $request,
    ): StreamedResponse {
        abort_unless(in_array($listing, $this->listings, true), 404);

        [$headers, $rows, $basename, $title] = match ($listing) {
            'email-providers' => $this->emailProviders($request),
            'sms-providers' => $this->smsProviders($request),
            'webhooks' => $this->webhooks($request),
            'user-sessions' => $this->userSessions($request),
            'activity-logs' => $this->activityLogs(),
            'background-jobs' => $this->backgroundJobs($request),
            'backups' => $this->backupsExport($request),
            default => abort(404),
        };

        return $this->downloadTabularExport($writer, $format, $basename, $headers, $rows, $title);
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function emailProviders(Request $request): array
    {
        Gate::authorize('viewAny', IntegrationEmailSetting::class);

        $settings = $this->scopeToTenant(IntegrationEmailSetting::query())
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->string('provider')))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderByDesc('is_active')
            ->orderBy('provider')
            ->limit(5000)
            ->get();

        $headers = [__('Provider'), __('From name'), __('From email'), __('Status'), __('Last tested')];
        $rows = $settings->map(fn (IntegrationEmailSetting $setting) => [
            $setting->provider->label(),
            $setting->from_name ?? '',
            $setting->from_email ?? '',
            $setting->is_active ? __('Active') : __('Inactive'),
            $setting->last_tested_at?->format('Y-m-d H:i') ?? '',
        ])->all();

        return [$headers, $rows, 'email-settings', __('Email settings')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function smsProviders(Request $request): array
    {
        Gate::authorize('viewAny', IntegrationSmsSetting::class);

        $settings = $this->scopeToTenant(IntegrationSmsSetting::query())
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->string('provider')))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderByDesc('is_active')
            ->orderBy('provider')
            ->limit(5000)
            ->get();

        $headers = [__('Provider'), __('Sender ID'), __('Status'), __('Health')];
        $rows = $settings->map(fn (IntegrationSmsSetting $setting) => [
            $setting->provider->label(),
            $setting->sender_id ?? '',
            $setting->is_active ? __('Active') : __('Inactive'),
            ucfirst((string) $setting->health_status),
        ])->all();

        return [$headers, $rows, 'sms-settings', __('SMS settings')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function webhooks(Request $request): array
    {
        Gate::authorize('viewAny', IntegrationWebhook::class);

        $webhooks = $this->scopeToTenant(IntegrationWebhook::query())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->limit(5000)
            ->get();

        $headers = [__('Name'), __('Endpoint'), __('Status'), __('Last delivery'), __('Response code')];
        $rows = $webhooks->map(fn (IntegrationWebhook $webhook) => [
            $webhook->name,
            $webhook->endpoint_url,
            $webhook->status->label(),
            $webhook->last_delivery_at?->format('Y-m-d H:i') ?? '',
            $webhook->last_response_code ?? '',
        ])->all();

        return [$headers, $rows, 'webhooks', __('Webhooks')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function userSessions(Request $request): array
    {
        Gate::authorize('viewAny', UserSessionRecord::class);

        $status = $request->string('status')->toString() ?: 'all';
        $search = $request->string('search')->toString() ?: null;

        $sessions = $this->userSessions
            ->paginate($status !== 'all' ? $status : null, $search, 5000)
            ->items();

        $headers = [
            __('Session ID'),
            __('User'),
            __('Email'),
            __('Role'),
            __('Company'),
            __('Branch'),
            __('IP address'),
            __('Device'),
            __('Browser'),
            __('Platform'),
            __('Login time'),
            __('Last activity'),
            __('Status'),
        ];

        $rows = collect($sessions)->map(fn (UserSessionRecord $session) => [
            (string) $session->id,
            $session->user?->name ?? '',
            $session->user?->email ?? '',
            $session->role_snapshot ?? '',
            $session->company?->name ?? '',
            $session->branch?->name ?? '',
            $session->ip_address ?? '',
            $session->device ?? '',
            $session->browser ?? '',
            $session->platform ?? '',
            $session->login_at?->format('Y-m-d H:i') ?? '',
            $session->last_activity_at?->format('Y-m-d H:i') ?? '',
            $session->status->label(),
        ])->all();

        return [$headers, $rows, 'user-sessions', __('User sessions')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function activityLogs(): array
    {
        Gate::authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::query()
            ->forTenant()
            ->with('user')
            ->latest('created_at')
            ->limit(5000)
            ->get();

        $headers = [__('When'), __('User'), __('Action'), __('Model'), __('IP address')];
        $rows = $logs->map(fn (ActivityLog $log) => [
            $log->created_at?->format('Y-m-d H:i:s') ?? '',
            $log->user?->name ?? '',
            $log->action,
            $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '',
            $log->ip_address ?? '',
        ])->all();

        return [$headers, $rows, 'activity-logs', __('Activity logs')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function backgroundJobs(Request $request): array
    {
        Gate::authorize('viewAny', BackgroundJobsCenter::class);

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
            'queue' => $request->string('queue')->toString() ?: 'all',
        ];

        $jobs = $this->backgroundJobs->allFiltered($filters);

        $headers = [
            __('Job ID'),
            __('Queue'),
            __('Type'),
            __('Status'),
            __('Started'),
            __('Completed'),
            __('Duration'),
            __('Attempts'),
            __('Error'),
        ];

        $rows = $jobs->map(fn (array $job) => [
            $job['job_id'],
            $job['queue'],
            $job['type']->shortLabel(),
            $job['status']->label(),
            $job['started_label'],
            $job['completed_label'],
            $job['duration_label'],
            (string) $job['attempts'],
            $job['error'] ?? '',
        ])->all();

        return [$headers, $rows, 'background-jobs', __('Background jobs')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function backupsExport(Request $request): array
    {
        Gate::authorize('viewAny', BackupsCenter::class);

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
        ];

        $backups = $this->backups->allFiltered($filters);

        $headers = [
            __('Backup name'),
            __('Type'),
            __('Size'),
            __('Created'),
            __('Retention date'),
            __('Status'),
        ];

        $rows = $backups->map(fn (SystemBackup $backup) => [
            $backup->name,
            $backup->type->shortLabel(),
            $this->backups->formatBytes($backup->size_bytes),
            $backup->backup_created_at?->format('Y-m-d H:i') ?? '',
            $backup->retention_until?->format('Y-m-d') ?? '',
            $backup->status->label(),
        ])->all();

        return [$headers, $rows, 'backups', __('Backups')];
    }
}
