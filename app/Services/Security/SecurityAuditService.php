<?php

namespace App\Services\Security;

use App\Enums\SecurityAuditRiskLevel;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Support\Security\UserAgentParser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityAuditService
{
    public function __construct(
        protected UserAgentParser $userAgentParser,
    ) {}

    public function record(
        string $action,
        ?Model $subject = null,
        ?int $userId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $description = null,
        ?SecurityAuditRiskLevel $risk = null,
        ?Request $request = null,
        ?string $module = null,
        ?string $entity = null,
        array $metadata = [],
    ): SecurityAuditEvent {
        $request ??= request();
        $parsed = $this->userAgentParser->parse($request->userAgent());
        $actor = $userId ? User::query()->find($userId) : auth()->user();
        $resolvedModule = $module ?? $this->resolveModule($action, $subject);
        $resolvedEntity = $entity ?? $this->resolveEntity($subject);
        $resolvedRisk = $risk ?? $this->resolveRiskLevel($action, $resolvedModule);
        $changedFields = $this->resolveChangedFields($before, $after);

        return SecurityAuditEvent::query()->create([
            'company_id' => $actor?->company_id ?? $subject?->company_id ?? null,
            'branch_id' => $actor?->default_branch_id ?? $subject?->branch_id ?? null,
            'user_id' => $userId ?? auth()->id(),
            'module' => $resolvedModule,
            'entity' => $resolvedEntity,
            'action' => $action,
            'description' => $description ?? $this->buildDescription($action, $resolvedEntity, $subject),
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $this->resolveSubjectLabel($subject),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'risk_level' => $resolvedRisk,
            'before_values' => $before ?: null,
            'after_values' => $after ?: null,
            'changed_fields' => $changedFields ?: null,
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with(['user', 'company', 'branch'])
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int>
     */
    public function dashboardMetrics(): array
    {
        $today = Carbon::today();
        $base = SecurityAuditEvent::query()->forTenant();

        return [
            'events_today' => (clone $base)->where('occurred_at', '>=', $today)->count(),
            'critical_events' => (clone $base)->where('risk_level', SecurityAuditRiskLevel::Critical)->where('occurred_at', '>=', $today)->count(),
            'failed_logins' => (clone $base)->where('action', 'failed_login')->where('occurred_at', '>=', $today)->count(),
            'permission_changes' => (clone $base)->whereIn('action', ['permission_assignment', 'permissions_updated'])->where('occurred_at', '>=', $today)->count(),
            'role_changes' => (clone $base)->where(function (Builder $query) {
                $query->whereIn('action', ['role_assignment', 'role_created', 'role_updated', 'role_deleted', 'role_deactivated', 'role_reactivated'])
                    ->orWhere(fn (Builder $inner) => $inner
                        ->where('module', 'roles')
                        ->whereIn('action', ['created', 'updated', 'deleted', 'deactivated', 'reactivated']));
            })->where('occurred_at', '>=', $today)->count(),
            'high_risk_actions' => (clone $base)->whereIn('risk_level', [SecurityAuditRiskLevel::High, SecurityAuditRiskLevel::Critical])->where('occurred_at', '>=', $today)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(string $format, array $filters = []): StreamedResponse
    {
        $filename = 'access-audit-'.now()->format('Y-m-d');

        return match ($format) {
            'excel' => $this->exportExcel($filename, $filters),
            'pdf' => $this->exportPdf($filename, $filters),
            default => $this->exportCsv($filename, $filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filteredQuery(array $filters): Builder
    {
        $query = SecurityAuditEvent::query()->forTenant();

        if (! empty($filters['date_from'])) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['role'])) {
            $query->whereHas('user.roles', fn (Builder $roleQuery) => $roleQuery->where('name', $filters['role']));
        }

        if (! empty($filters['module']) && $filters['module'] !== 'all') {
            $query->where('module', $filters['module']);
        }

        if (! empty($filters['entity']) && $filters['entity'] !== 'all') {
            $query->where('entity', $filters['entity']);
        }

        if (! empty($filters['branch_id']) && $filters['branch_id'] !== 'all') {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['risk_level']) && $filters['risk_level'] !== 'all') {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.Str::lower($filters['search']).'%';
            $query->where(function (Builder $inner) use ($search) {
                $inner->whereRaw('LOWER(action) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(entity) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(module) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(subject_label) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(ip_address) LIKE ?', [$search])
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                        ->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]));
            });
        }

        return $query;
    }

    protected function resolveModule(string $action, ?Model $subject): string
    {
        if (in_array($action, ['login', 'logout', 'failed_login', 'password_reset', 'password_change', 'two_factor_enabled', 'two_factor_disabled', 'session_revoked', 'force_logout', 'locked_out'], true)) {
            return 'authentication';
        }

        if (in_array($action, ['created', 'updated', 'deleted', 'activated', 'deactivated', 'role_assignment', 'permission_assignment', 'branch_assignment', 'permissions_updated', 'role_created', 'role_updated', 'role_deleted', 'role_deactivated', 'role_reactivated'], true)) {
            if ($subject instanceof User) {
                return 'users';
            }

            if ($subject instanceof \Spatie\Permission\Models\Role) {
                return 'roles';
            }
        }

        return match ($subject ? class_basename($subject) : '') {
            'Customer' => 'crm',
            'Quotation' => 'commercial',
            'SalesOrder' => 'commercial',
            'ProductionJobCard' => 'production',
            'InventoryItem', 'StockAdjustment' => 'inventory',
            'PurchaseOrder', 'PurchaseRequest' => 'procurement',
            'AccountingPeriod', 'FiscalYear' => 'accounting',
            'Company', 'Branch' => 'organization',
            default => 'system',
        };
    }

    protected function resolveEntity(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        return Str::snake(class_basename($subject));
    }

    protected function resolveRiskLevel(string $action, string $module): SecurityAuditRiskLevel
    {
        return match ($action) {
            'permission_assignment', 'permissions_updated', 'force_logout', 'locked_out', 'period_closed' => SecurityAuditRiskLevel::Critical,
            'role_assignment', 'role_deleted', 'deleted', 'failed_login', 'purchase_approved', 'quote_approved', 'inventory_adjusted', 'settings_changed', 'number_series_changed', 'session_revoked' => SecurityAuditRiskLevel::High,
            'password_reset', 'password_change', 'updated', 'role_updated', 'role_created', 'logout', 'two_factor_enabled', 'two_factor_disabled' => SecurityAuditRiskLevel::Medium,
            default => SecurityAuditRiskLevel::Low,
        };
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @return list<string>
     */
    protected function resolveChangedFields(?array $before, ?array $after): array
    {
        if ($before === null || $after === null) {
            return [];
        }

        $changed = [];

        foreach ($after as $key => $value) {
            if (! array_key_exists($key, $before) || $before[$key] !== $value) {
                $changed[] = (string) $key;
            }
        }

        return $changed;
    }

    protected function buildDescription(string $action, ?string $entity, ?Model $subject): string
    {
        $label = $subject ? $this->resolveSubjectLabel($subject) : null;
        $entityLabel = $entity ? Str::headline($entity) : __('Record');

        return match ($action) {
            'login' => __('User signed in'),
            'logout' => __('User signed out'),
            'failed_login' => __('Failed login attempt'),
            'password_reset' => __('Password reset'),
            'password_change' => __('Password changed'),
            'permission_assignment', 'permissions_updated' => __('Permission assignment changed'),
            'role_assignment' => __('Role assignment changed'),
            'branch_assignment' => __('Branch assignment changed'),
            'session_revoked' => __('Session revoked'),
            'force_logout' => __('Force logout executed'),
            'locked_out' => __('Account lockout triggered'),
            'period_closed' => __('Accounting period closed'),
            'number_series_changed' => __('Number series changed'),
            'purchase_approved' => __('Purchase approved'),
            'settings_changed' => __('Settings changed'),
            'created' => __(':entity created', ['entity' => $entityLabel]),
            'updated' => __(':entity updated', ['entity' => $entityLabel]),
            'deleted' => __(':entity deleted', ['entity' => $entityLabel]),
            default => $label
                ? __(':action on :label', ['action' => Str::headline($action), 'label' => $label])
                : Str::headline($action),
        };
    }

    protected function resolveSubjectLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        foreach (['name', 'title', 'reference_number', 'code', 'email', 'session_number', 'number'] as $attribute) {
            if (! empty($subject->{$attribute})) {
                return (string) $subject->{$attribute};
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportCsv(string $filename, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Timestamp', 'User', 'Module', 'Entity', 'Action', 'Description',
                'IP Address', 'Device', 'Browser', 'Company', 'Branch', 'Risk Level',
            ]);

            $this->filteredQuery($filters)
                ->with(['user', 'company', 'branch'])
                ->orderByDesc('occurred_at')
                ->chunk(200, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            $event->occurred_at?->toDateTimeString(),
                            $event->user?->name ?? $event->user?->email,
                            $event->module,
                            $event->entity,
                            $event->action,
                            $event->description,
                            $event->ip_address,
                            $event->device,
                            $event->browser,
                            $event->company?->name,
                            $event->branch?->name,
                            $event->risk_level->value,
                        ]);
                    }
                });

            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportExcel(string $filename, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($filters) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><thead><tr>';
            foreach (['Timestamp', 'User', 'Module', 'Entity', 'Action', 'Description', 'IP Address', 'Device', 'Browser', 'Company', 'Branch', 'Risk Level'] as $header) {
                echo '<th>'.e($header).'</th>';
            }
            echo '</tr></thead><tbody>';

            $this->filteredQuery($filters)
                ->with(['user', 'company', 'branch'])
                ->orderByDesc('occurred_at')
                ->chunk(200, function ($events) {
                    foreach ($events as $event) {
                        echo '<tr>';
                        foreach ([
                            $event->occurred_at?->toDateTimeString(),
                            $event->user?->name ?? $event->user?->email,
                            $event->module,
                            $event->entity,
                            $event->action,
                            $event->description,
                            $event->ip_address,
                            $event->device,
                            $event->browser,
                            $event->company?->name,
                            $event->branch?->name,
                            $event->risk_level->value,
                        ] as $cell) {
                            echo '<td>'.e((string) $cell).'</td>';
                        }
                        echo '</tr>';
                    }
                });

            echo '</tbody></table>';
        }, "{$filename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportPdf(string $filename, array $filters): StreamedResponse
    {
        $events = $this->filteredQuery($filters)
            ->with(['user', 'company', 'branch'])
            ->orderByDesc('occurred_at')
            ->limit(500)
            ->get();

        $html = view('admin.security.audit.exports.pdf', [
            'events' => $events,
            'generatedAt' => now(),
            'filters' => $filters,
        ])->render();

        return response()->streamDownload(fn () => print($html), "{$filename}.html", [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
