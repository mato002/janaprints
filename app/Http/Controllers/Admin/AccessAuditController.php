<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccessAuditController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected SecurityAuditService $auditService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SecurityAuditEvent::class);

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'role' => $request->string('role')->toString() ?: null,
            'module' => $request->string('module')->toString() ?: 'all',
            'entity' => $request->string('entity')->toString() ?: 'all',
            'branch_id' => $request->string('branch_id')->toString() ?: 'all',
            'risk_level' => $request->string('risk_level')->toString() ?: 'all',
        ];

        return view('admin.security.audit.index', [
            'events' => $this->auditService->paginate($filters),
            'metrics' => $this->auditService->dashboardMetrics(),
            'filters' => $filters,
            'users' => $this->scopeToTenant(User::query())->orderBy('name')->get(['id', 'name', 'email']),
            'roles' => Role::query()->where('guard_name', 'web')->where('name', '!=', 'Super Admin')->orderBy('name')->pluck('name'),
            'branches' => $this->scopeToTenant(Branch::query())->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'modules' => $this->moduleOptions(),
            'entities' => $this->entityOptions(),
            'canExport' => $request->user()->can('export', SecurityAuditEvent::class),
        ]);
    }

    public function show(SecurityAuditEvent $securityAuditEvent): JsonResponse
    {
        $this->authorize('view', $securityAuditEvent);

        $securityAuditEvent->load(['user', 'company', 'branch']);

        return response()->json([
            'id' => $securityAuditEvent->id,
            'occurred_at' => $securityAuditEvent->occurred_at?->toIso8601String(),
            'occurred_at_formatted' => $securityAuditEvent->occurred_at?->format('M j, Y g:i A'),
            'user' => $securityAuditEvent->user ? [
                'name' => $securityAuditEvent->user->name,
                'email' => $securityAuditEvent->user->email,
            ] : null,
            'module' => $securityAuditEvent->module,
            'entity' => $securityAuditEvent->entity,
            'action' => $securityAuditEvent->action,
            'description' => $securityAuditEvent->description,
            'subject_label' => $securityAuditEvent->subject_label,
            'ip_address' => $securityAuditEvent->ip_address,
            'device' => $securityAuditEvent->device,
            'browser' => $securityAuditEvent->browser,
            'platform' => $securityAuditEvent->platform,
            'company' => $securityAuditEvent->company?->name,
            'branch' => $securityAuditEvent->branch?->name,
            'risk_level' => $securityAuditEvent->risk_level->value,
            'risk_label' => $securityAuditEvent->risk_level->label(),
            'before_values' => $securityAuditEvent->before_values,
            'after_values' => $securityAuditEvent->after_values,
            'changed_fields' => $securityAuditEvent->changed_fields,
            'metadata' => $securityAuditEvent->metadata,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', SecurityAuditEvent::class);

        $format = $request->string('format')->toString() ?: 'csv';
        $filters = $request->only(['search', 'date_from', 'date_to', 'user_id', 'role', 'module', 'entity', 'branch_id', 'risk_level']);

        return $this->auditService->export($format, $filters);
    }

    /**
     * @return array<string, string>
     */
    protected function moduleOptions(): array
    {
        return [
            'all' => __('All modules'),
            'authentication' => __('Authentication'),
            'users' => __('Users'),
            'roles' => __('Roles'),
            'crm' => __('CRM'),
            'commercial' => __('Commercial'),
            'production' => __('Production'),
            'inventory' => __('Inventory'),
            'procurement' => __('Procurement'),
            'accounting' => __('Accounting'),
            'organization' => __('Organization'),
            'system' => __('System'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function entityOptions(): array
    {
        return [
            'all' => __('All entities'),
            'user' => __('User'),
            'role' => __('Role'),
            'customer' => __('Customer'),
            'quotation' => __('Quotation'),
            'sales_order' => __('Sales Order'),
            'production_job_card' => __('Job Card'),
            'inventory_item' => __('Inventory Item'),
            'purchase_order' => __('Purchase Order'),
            'accounting_period' => __('Accounting Period'),
        ];
    }
}
