<?php

namespace App\Support\Platform;

use App\Enums\DocumentModule;
use App\Enums\DocumentTypeStatus;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\DocumentTypeDefinition;
use App\Models\Platform\NumberingSequence;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DocumentTypesManager
{
    public function ensureDefinitions(int $companyId, ?int $branchId): void
    {
        $registry = config('document_types_registry.types', []);

        foreach ($registry as $code => $definition) {
            DocumentTypeDefinition::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'code' => $code,
                ],
                [
                    'name' => $definition['name'],
                    'module' => $definition['module'],
                    'prefix' => $definition['prefix'],
                    'number_series_key' => $definition['number_series_key'],
                    'approval_required' => $definition['approval_required'],
                    'approval_levels' => $definition['approval_levels'],
                    'approval_rule_type' => $definition['approval_rule_type'],
                    'retention_period_days' => $definition['retention_period_days'],
                    'auto_numbering' => $definition['auto_numbering'],
                    'status' => DocumentTypeStatus::Active,
                    'form_key' => $definition['form_key'] ?? null,
                    'workflow_json' => $definition['workflow'] ?? null,
                    'is_system' => true,
                ],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function dashboardRows(int $companyId, ?int $branchId): array
    {
        $this->ensureDefinitions($companyId, $branchId);

        return DocumentTypeDefinition::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->map(fn (DocumentTypeDefinition $definition) => $this->presentRow($definition, $companyId, $branchId))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(DocumentTypeDefinition $definition, int $companyId, ?int $branchId): array
    {
        $numberSeries = $this->resolveNumberSeriesLabel($definition, $companyId, $branchId);
        $approvalRule = $this->resolveApprovalLabel($definition, $companyId, $branchId);

        return [
            'id' => $definition->id,
            'code' => $definition->code,
            'name' => $definition->name,
            'module' => $definition->module->label(),
            'module_key' => $definition->module->value,
            'prefix' => $definition->prefix,
            'number_series' => $numberSeries,
            'number_series_key' => $definition->number_series_key,
            'approval_required' => $definition->approval_required,
            'approval_levels' => $definition->approval_levels,
            'approval_rule' => $approvalRule,
            'approval_rule_type' => $definition->approval_rule_type,
            'retention_period' => $definition->retentionLabel(),
            'retention_period_days' => $definition->retention_period_days,
            'auto_numbering' => $definition->auto_numbering,
            'status' => $definition->status->label(),
            'status_key' => $definition->status->value,
            'is_active' => $definition->isActive(),
            'is_system' => $definition->is_system,
            'form_key' => $definition->form_key,
            'workflow' => $definition->workflow_json ?? [],
        ];
    }

    public function create(int $companyId, ?int $branchId, array $data): DocumentTypeDefinition
    {
        $this->assertUniqueCode($companyId, $branchId, $data['code']);

        return DocumentTypeDefinition::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $data['code'],
            'name' => $data['name'],
            'module' => $data['module'],
            'prefix' => $data['prefix'] ?? null,
            'number_series_key' => $data['number_series_key'] ?? null,
            'approval_required' => (bool) ($data['approval_required'] ?? false),
            'approval_levels' => (int) ($data['approval_levels'] ?? 0),
            'approval_rule_type' => $data['approval_rule_type'] ?? null,
            'retention_period_days' => $data['retention_period_days'] ?? null,
            'auto_numbering' => (bool) ($data['auto_numbering'] ?? true),
            'status' => DocumentTypeStatus::Active,
            'form_key' => $data['form_key'] ?? null,
            'workflow_json' => $this->normalizeWorkflow($data),
            'is_system' => false,
        ]);
    }

    public function update(DocumentTypeDefinition $definition, array $data): DocumentTypeDefinition
    {
        $definition->fill([
            'name' => $data['name'],
            'module' => $data['module'],
            'prefix' => $data['prefix'] ?? null,
            'number_series_key' => $data['number_series_key'] ?? null,
            'approval_required' => (bool) ($data['approval_required'] ?? false),
            'approval_levels' => (int) ($data['approval_levels'] ?? 0),
            'approval_rule_type' => $data['approval_rule_type'] ?? null,
            'retention_period_days' => $data['retention_period_days'] ?? null,
            'auto_numbering' => (bool) ($data['auto_numbering'] ?? true),
            'form_key' => $data['form_key'] ?? null,
            'workflow_json' => $this->normalizeWorkflow($data),
        ]);

        if (! $definition->is_system && isset($data['code'])) {
            $this->assertUniqueCode(
                $definition->company_id,
                $definition->branch_id,
                $data['code'],
                $definition->id,
            );
            $definition->code = $data['code'];
        }

        $definition->save();

        return $definition->fresh();
    }

    public function activate(DocumentTypeDefinition $definition): DocumentTypeDefinition
    {
        $definition->update(['status' => DocumentTypeStatus::Active]);

        return $definition->fresh();
    }

    public function deactivate(DocumentTypeDefinition $definition): DocumentTypeDefinition
    {
        $definition->update(['status' => DocumentTypeStatus::Inactive]);

        return $definition->fresh();
    }

    public function modules(): Collection
    {
        return collect(DocumentModule::cases())
            ->mapWithKeys(fn (DocumentModule $module) => [$module->value => $module->label()]);
    }

    public function numberSeriesOptions(): Collection
    {
        return collect(config('numbering_registry.document_types', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label'] ?? $key]);
    }

    public function approvalRuleOptions(): Collection
    {
        return collect(config('approval_registry.rule_types', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label'] ?? $key]);
    }

    public function formOptions(): Collection
    {
        return collect(config('form_registry.forms', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label'] ?? $key]);
    }

    protected function resolveNumberSeriesLabel(
        DocumentTypeDefinition $definition,
        int $companyId,
        ?int $branchId,
    ): string {
        if (! $definition->number_series_key) {
            return __('Not linked');
        }

        $registryLabel = config("numbering_registry.document_types.{$definition->number_series_key}.label");
        $sequence = NumberingSequence::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('document_type', $definition->number_series_key)
            ->first();

        $label = $registryLabel ?: $definition->number_series_key;

        if (! $sequence) {
            return $label.' ('.__('no sequence').')';
        }

        return $label.' (#'.number_format($sequence->next_number).')';
    }

    protected function resolveApprovalLabel(
        DocumentTypeDefinition $definition,
        int $companyId,
        ?int $branchId,
    ): string {
        if (! $definition->approval_required) {
            return __('Not required');
        }

        if (! $definition->approval_rule_type) {
            return __('Required (:levels levels)', ['levels' => $definition->approval_levels]);
        }

        $registryLabel = config("approval_registry.rule_types.{$definition->approval_rule_type}.label");
        $rule = $this->findApprovalRuleForDisplay($companyId, $branchId, $definition->approval_rule_type);

        $label = $registryLabel ?: $definition->approval_rule_type;

        if (! $rule) {
            return $label.' ('.__('not configured').')';
        }

        return $label.($rule->is_enabled ? '' : ' ('.__('disabled').')');
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeWorkflow(array $data): array
    {
        return [
            'approval_workflow' => $data['approval_workflow'] ?? null,
            'notification_workflow' => $data['notification_workflow'] ?? null,
            'audit_tracking' => (bool) ($data['audit_tracking'] ?? true),
            'archival_rules' => $data['archival_rules'] ?? null,
            'print_template' => $data['print_template'] ?? null,
        ];
    }

    protected function findApprovalRuleForDisplay(
        int $companyId,
        ?int $branchId,
        ?string $ruleType,
    ): ?ApprovalRule {
        if (! $ruleType) {
            return null;
        }

        $query = ApprovalRule::query()
            ->where('company_id', $companyId)
            ->where('rule_type', $ruleType);

        if ($branchId) {
            $branchRule = (clone $query)->where('branch_id', $branchId)->first();

            if ($branchRule) {
                return $branchRule;
            }
        }

        return $query->whereNull('branch_id')->first();
    }

    protected function assertUniqueCode(
        int $companyId,
        ?int $branchId,
        string $code,
        ?int $ignoreId = null,
    ): void {
        $exists = DocumentTypeDefinition::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('code', $code)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException(__('A document type with code [:code] already exists.', ['code' => $code]));
        }
    }
}
