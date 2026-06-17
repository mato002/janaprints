<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentModule;
use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Admin\Concerns\PreservesWorkspaceEmbed;
use App\Http\Controllers\Controller;
use App\Models\Platform\DocumentTypeDefinition;
use App\Services\Security\SecurityAuditService;
use App\Support\Platform\DocumentTypesManager;
use App\Support\Platform\SettingsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class DocumentTypesSettingsController extends Controller
{
    use PreservesWorkspaceEmbed;
    use ResolvesSettingsScope;

    public function __construct(
        protected DocumentTypesManager $manager,
        protected SettingsRegistry $registry,
        protected SecurityAuditService $auditService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DocumentTypeDefinition::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.settings.document-types.index', [
            'sections' => $this->registry->sections(),
            'rows' => $this->manager->dashboardRows($companyId, $branchId),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canCreate' => auth()->user()->can('create', DocumentTypeDefinition::class),
            'canEdit' => auth()->user()->can('configuration.document_types.edit'),
            'canActivate' => auth()->user()->can('configuration.document_types.activate'),
            'canDeactivate' => auth()->user()->can('configuration.document_types.deactivate'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', DocumentTypeDefinition::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.settings.document-types.create', $this->formContext($companyId, $branchId));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DocumentTypeDefinition::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $validated = $this->validatePayload($request);

        try {
            $definition = $this->manager->create($companyId, $branchId, $validated);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['code' => $exception->getMessage()]);
        }

        $this->auditService->record(
            action: 'document_type.created',
            subject: $definition,
            after: $definition->only([
                'code', 'name', 'module', 'prefix', 'number_series_key',
                'approval_required', 'approval_levels', 'status',
            ]),
            module: 'configuration',
            entity: 'document_type',
        );

        return redirect()
            ->route('admin.settings.document-types.index', $this->scopeParams($companyId, $branchId, $request))
            ->with('status', __('Document type created.'));
    }

    public function edit(Request $request, DocumentTypeDefinition $documentTypeDefinition): View
    {
        $this->authorize('view', $documentTypeDefinition);
        $this->assertScope($request, $documentTypeDefinition);

        return view('admin.settings.document-types.edit', array_merge(
            $this->formContext($documentTypeDefinition->company_id, $documentTypeDefinition->branch_id),
            ['documentType' => $documentTypeDefinition],
        ));
    }

    public function update(Request $request, DocumentTypeDefinition $documentTypeDefinition): RedirectResponse
    {
        $this->authorize('update', $documentTypeDefinition);
        $this->assertScope($request, $documentTypeDefinition);

        $validated = $this->validatePayload($request, $documentTypeDefinition);
        $before = $documentTypeDefinition->only([
            'code', 'name', 'module', 'prefix', 'number_series_key',
            'approval_required', 'approval_levels', 'approval_rule_type',
            'retention_period_days', 'auto_numbering', 'status', 'workflow_json',
        ]);

        try {
            $definition = $this->manager->update($documentTypeDefinition, $validated);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['code' => $exception->getMessage()]);
        }

        $this->auditService->record(
            action: 'document_type.updated',
            subject: $definition,
            before: $before,
            after: $definition->only([
                'code', 'name', 'module', 'prefix', 'number_series_key',
                'approval_required', 'approval_levels', 'approval_rule_type',
                'retention_period_days', 'auto_numbering', 'status', 'workflow_json',
            ]),
            module: 'configuration',
            entity: 'document_type',
        );

        return redirect()
            ->route('admin.settings.document-types.index', $this->scopeParams($definition->company_id, $definition->branch_id, $request))
            ->with('status', __('Document type updated.'));
    }

    public function activate(Request $request, DocumentTypeDefinition $documentTypeDefinition): RedirectResponse
    {
        $this->authorize('activate', $documentTypeDefinition);
        $this->assertScope($request, $documentTypeDefinition);

        $definition = $this->manager->activate($documentTypeDefinition);

        $this->auditService->record(
            action: 'document_type.activated',
            subject: $definition,
            module: 'configuration',
            entity: 'document_type',
        );

        return redirect()
            ->route('admin.settings.document-types.index', $this->scopeParams($definition->company_id, $definition->branch_id, $request))
            ->with('status', __('Document type activated.'));
    }

    public function deactivate(Request $request, DocumentTypeDefinition $documentTypeDefinition): RedirectResponse
    {
        $this->authorize('deactivate', $documentTypeDefinition);
        $this->assertScope($request, $documentTypeDefinition);

        $definition = $this->manager->deactivate($documentTypeDefinition);

        $this->auditService->record(
            action: 'document_type.deactivated',
            subject: $definition,
            module: 'configuration',
            entity: 'document_type',
        );

        return redirect()
            ->route('admin.settings.document-types.index', $this->scopeParams($definition->company_id, $definition->branch_id, $request))
            ->with('status', __('Document type deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formContext(int $companyId, ?int $branchId): array
    {
        return [
            'sections' => $this->registry->sections(),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'modules' => $this->manager->modules(),
            'numberSeriesOptions' => $this->manager->numberSeriesOptions(),
            'approvalRuleOptions' => $this->manager->approvalRuleOptions(),
            'formOptions' => $this->manager->formOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request, ?DocumentTypeDefinition $documentType = null): array
    {
        $moduleValues = array_map(fn (DocumentModule $module) => $module->value, DocumentModule::cases());

        return $request->validate([
            'code' => [
                $documentType?->is_system ? 'nullable' : 'required',
                'string',
                'max:50',
                'alpha_dash',
            ],
            'name' => ['required', 'string', 'max:120'],
            'module' => ['required', Rule::in($moduleValues)],
            'prefix' => ['nullable', 'string', 'max:20'],
            'number_series_key' => ['nullable', 'string', 'max:50'],
            'approval_required' => ['nullable', 'boolean'],
            'approval_levels' => ['nullable', 'integer', 'min:0', 'max:10'],
            'approval_rule_type' => ['nullable', 'string', 'max:50'],
            'retention_period_days' => ['nullable', 'integer', 'min:1', 'max:36500'],
            'auto_numbering' => ['nullable', 'boolean'],
            'form_key' => ['nullable', 'string', 'max:50'],
            'approval_workflow' => ['nullable', 'string', 'max:80'],
            'notification_workflow' => ['nullable', 'string', 'max:80'],
            'audit_tracking' => ['nullable', 'boolean'],
            'archival_rules' => ['nullable', 'string', 'max:80'],
            'print_template' => ['nullable', 'string', 'max:80'],
        ]);
    }

    protected function assertScope(Request $request, DocumentTypeDefinition $documentType): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        abort_unless($documentType->company_id === $companyId, 404);
        abort_unless($documentType->branch_id === $branchId, 404);
    }

    /**
     * @return array<string, int|string|null>
     */
    protected function scopeParams(int $companyId, ?int $branchId, ?Request $request = null): array
    {
        return $this->workspaceEmbedParams([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ], $request);
    }
}
