<?php

namespace App\Services\Operations;

use App\Enums\ComplianceAuditCategory;
use App\Enums\SecurityAuditRiskLevel;
use App\Models\SecurityAuditEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Export\PdfExportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceAuditService
{
    public function __construct(
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with(['user'])
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int>
     */
    public function summaryMetrics(): array
    {
        $base = $this->complianceScope(SecurityAuditEvent::query()->forTenant());
        $today = Carbon::today();

        return [
            'events_today' => (clone $base)->where('occurred_at', '>=', $today)->count(),
            'critical_events' => (clone $base)->where('risk_level', SecurityAuditRiskLevel::Critical)->count(),
            'permission_changes' => (clone $base)->whereIn('action', ['permission_assignment', 'permissions_updated'])->count(),
            'configuration_changes' => (clone $base)->whereIn('action', [
                'settings_changed',
                'number_series_changed',
                'document_type.created',
                'document_type.updated',
                'document_type.activated',
                'document_type.deactivated',
            ])->count(),
            'inventory_adjustments' => (clone $base)->where('action', 'inventory_adjusted')->count(),
            'accounting_events' => (clone $base)->whereIn('action', ['period_closed', 'journal_posted', 'payment_reversed'])->count(),
        ];
    }

    public function find(int $eventId): SecurityAuditEvent
    {
        return $this->complianceScope(SecurityAuditEvent::query()->forTenant())
            ->with(['user', 'company', 'branch'])
            ->findOrFail($eventId);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(string $format, array $filters = []): StreamedResponse
    {
        $filename = 'audit-logs-'.now()->format('Y-m-d');

        return match ($format) {
            'excel' => $this->exportExcel($filename, $filters),
            'pdf' => $this->exportPdf($filename, $filters),
            default => $this->exportCsv($filename, $filters),
        };
    }

    public function actionLabel(SecurityAuditEvent $event): string
    {
        if ($event->action === 'created' && $event->entity === 'user') {
            return ComplianceAuditCategory::UserCreated->label();
        }

        if (in_array($event->action, ComplianceAuditCategory::RoleChanged->actions(), true)
            && ($event->entity === 'role' || $event->module === 'roles')) {
            return ComplianceAuditCategory::RoleChanged->label();
        }

        return match ($event->action) {
            'permission_assignment', 'permissions_updated' => ComplianceAuditCategory::PermissionChanged->label(),
            'settings_changed' => ComplianceAuditCategory::SettingsChanged->label(),
            'number_series_changed' => ComplianceAuditCategory::NumberSeriesChanged->label(),
            'document_type.created', 'document_type.updated', 'document_type.activated', 'document_type.deactivated' => ComplianceAuditCategory::DocumentTypeChanged->label(),
            'inventory_adjusted' => ComplianceAuditCategory::InventoryAdjusted->label(),
            'period_closed' => ComplianceAuditCategory::AccountingPeriodClosed->label(),
            'journal_posted' => ComplianceAuditCategory::JournalPosted->label(),
            'payment_reversed' => ComplianceAuditCategory::PaymentReversed->label(),
            default => Str::headline(str_replace(['.', '_'], ' ', $event->action)),
        };
    }

    public function formatValues(?array $values): string
    {
        if ($values === null || $values === []) {
            return '—';
        }

        return Str::limit(json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—', 120);
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(): array
    {
        $options = ['all' => __('All compliance events')];

        foreach (ComplianceAuditCategory::cases() as $category) {
            $options[$category->value] = $category->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function moduleOptions(): array
    {
        return [
            'all' => __('All modules'),
            'users' => __('Users'),
            'roles' => __('Roles'),
            'configuration' => __('Configuration'),
            'inventory' => __('Inventory'),
            'accounting' => __('Accounting'),
            'organization' => __('Organization'),
            'system' => __('System'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function riskOptions(): array
    {
        $options = ['all' => __('All levels')];

        foreach (SecurityAuditRiskLevel::cases() as $level) {
            $options[$level->value] = $level->label();
        }

        return $options;
    }

    public function complianceScope(Builder $query): Builder
    {
        return $query->where(function (Builder $scope) {
            $scope->whereIn('action', [
                'permission_assignment',
                'permissions_updated',
                'settings_changed',
                'number_series_changed',
                'document_type.created',
                'document_type.updated',
                'document_type.activated',
                'document_type.deactivated',
                'inventory_adjusted',
                'period_closed',
                'journal_posted',
                'payment_reversed',
                'role_assignment',
                'role_created',
                'role_updated',
                'role_deleted',
                'role_deactivated',
                'role_reactivated',
            ])->orWhere(function (Builder $inner) {
                $inner->where('action', 'created')->where('entity', 'user');
            })->orWhere(function (Builder $inner) {
                $inner->whereIn('action', ['created', 'updated', 'deleted', 'deactivated', 'reactivated'])
                    ->where('entity', 'role');
            });
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filteredQuery(array $filters): Builder
    {
        $query = $this->complianceScope(SecurityAuditEvent::query()->forTenant());

        if (! empty($filters['date_from'])) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['module']) && $filters['module'] !== 'all') {
            $query->where('module', $filters['module']);
        }

        if (! empty($filters['risk_level']) && $filters['risk_level'] !== 'all') {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $category = ComplianceAuditCategory::from($filters['category']);
            $query->where(function (Builder $inner) use ($category) {
                if ($category === ComplianceAuditCategory::UserCreated) {
                    $inner->where('action', 'created')->where('entity', 'user');

                    return;
                }

                if ($category === ComplianceAuditCategory::RoleChanged) {
                    $inner->where(function (Builder $roleScope) {
                        $roleScope->whereIn('action', ComplianceAuditCategory::RoleChanged->actions())
                            ->where(function (Builder $entityScope) {
                                $entityScope->where('entity', 'role')->orWhere('module', 'roles');
                            });
                    });

                    return;
                }

                $inner->whereIn('action', $category->actions());
            });
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

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportCsv(string $filename, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Timestamp', 'User', 'Module', 'Entity', 'Action', 'Old Value', 'New Value',
                'IP Address', 'Device', 'Risk Level',
            ]);

            $this->filteredQuery($filters)
                ->with(['user'])
                ->orderByDesc('occurred_at')
                ->chunk(200, function ($events) use ($handle) {
                    foreach ($events as $event) {
                        fputcsv($handle, [
                            $event->occurred_at?->toDateTimeString(),
                            $event->user?->name ?? $event->user?->email,
                            $event->module,
                            $event->entity,
                            $this->actionLabel($event),
                            $this->serializeValues($event->before_values),
                            $this->serializeValues($event->after_values),
                            $event->ip_address,
                            $event->device,
                            $event->risk_level->value,
                        ]);
                    }
                });

            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportExcel(string $filename, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($filters) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><thead><tr>';
            foreach ([
                'Timestamp', 'User', 'Module', 'Entity', 'Action', 'Old Value', 'New Value',
                'IP Address', 'Device', 'Risk Level',
            ] as $header) {
                echo '<th>'.e($header).'</th>';
            }
            echo '</tr></thead><tbody>';

            $this->filteredQuery($filters)
                ->with(['user'])
                ->orderByDesc('occurred_at')
                ->chunk(200, function ($events) {
                    foreach ($events as $event) {
                        echo '<tr>';
                        foreach ([
                            $event->occurred_at?->toDateTimeString(),
                            $event->user?->name ?? $event->user?->email,
                            $event->module,
                            $event->entity,
                            $this->actionLabel($event),
                            $this->serializeValues($event->before_values),
                            $this->serializeValues($event->after_values),
                            $event->ip_address,
                            $event->device,
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
            ->with(['user'])
            ->orderByDesc('occurred_at')
            ->limit(500)
            ->get();

        return $this->pdfExports->downloadHtml(
            $filename,
            view('admin.operations.audit.exports.pdf', [
                'events' => $events,
                'generatedAt' => now(),
                'filters' => $filters,
                'auditService' => $this,
            ])->render(),
            'landscape',
        );
    }

    protected function serializeValues(?array $values): string
    {
        if ($values === null || $values === []) {
            return '';
        }

        return json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
}
