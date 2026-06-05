<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Operations\AuditLogsCenter;
use App\Services\Operations\ComplianceAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogsController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected ComplianceAuditService $auditService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLogsCenter::class);

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'module' => $request->string('module')->toString() ?: 'all',
            'category' => $request->string('category')->toString() ?: 'all',
            'risk_level' => $request->string('risk_level')->toString() ?: 'all',
        ];

        return view('admin.operations.audit.index', [
            'events' => $this->auditService->paginate($filters),
            'metrics' => $this->auditService->summaryMetrics(),
            'filters' => $filters,
            'users' => $this->scopeToTenant(User::query())->orderBy('name')->get(['id', 'name', 'email']),
            'categoryOptions' => $this->auditService->categoryOptions(),
            'moduleOptions' => $this->auditService->moduleOptions(),
            'riskOptions' => $this->auditService->riskOptions(),
            'canExport' => $request->user()->can('export', AuditLogsCenter::class),
            'auditService' => $this->auditService,
        ]);
    }

    public function show(int $securityAuditEvent): JsonResponse
    {
        $this->authorize('view', AuditLogsCenter::class);

        $event = $this->auditService->find($securityAuditEvent);

        return response()->json([
            'id' => $event->id,
            'occurred_at_formatted' => $event->occurred_at?->format('M j, Y g:i A'),
            'user' => $event->user ? [
                'name' => $event->user->name,
                'email' => $event->user->email,
            ] : null,
            'module' => $event->module,
            'entity' => $event->entity,
            'action' => $this->auditService->actionLabel($event),
            'description' => $event->description,
            'old_value' => $event->before_values,
            'new_value' => $event->after_values,
            'changed_fields' => $event->changed_fields,
            'ip_address' => $event->ip_address,
            'device' => $event->device,
            'browser' => $event->browser,
            'platform' => $event->platform,
            'risk_level' => $event->risk_level->value,
            'risk_label' => $event->risk_level->label(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', AuditLogsCenter::class);

        $format = $request->string('format')->toString() ?: 'csv';
        $filters = $request->only(['search', 'date_from', 'date_to', 'user_id', 'module', 'category', 'risk_level']);

        return $this->auditService->export($format, $filters);
    }
}
