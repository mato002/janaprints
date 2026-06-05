<?php

namespace Database\Seeders;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalRuleType;
use App\Enums\DocumentType;
use App\Enums\EscalationMethod;
use App\Enums\EscalationRuleStatus;
use App\Enums\WorkflowRuleActionType;
use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainStep;
use App\Models\Governance\WorkflowEscalationRule;
use App\Models\Governance\WorkflowRule;
use App\Models\Governance\WorkflowRuleAction;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\NumberingSequence;
use App\Models\Platform\SystemSetting;
use App\Support\Platform\DocumentTypesManager;
use App\Support\Platform\FormSettingsManager;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PlatformConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            $this->command?->warn('Platform configuration skipped: JANA company not found.');

            return;
        }

        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->first();

        $this->seedSystemSettings($company->id, $branch?->id);
        $this->seedNumberingSequences($company->id, $branch?->id);
        $this->seedFormSettings($company->id);
        $this->seedApprovalRules($company->id, null);
        $this->seedApprovalChains($company->id, null);
        $this->seedEscalationRules($company->id, null);
        $this->seedWorkflowRules($company->id, null);
        $this->seedDocumentTypes($company->id, $branch?->id);
    }

    protected function seedSystemSettings(int $companyId, ?int $branchId): void
    {
        $defaults = [
            'quotation_validity_days' => 30,
            'default_payment_terms' => 'Net 30',
            'default_tax_rate' => 16,
            'fiscal_year_start_month' => 1,
            'inventory_allow_negative_stock' => false,
            'artwork_requires_customer_approval' => true,
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['company_id' => $companyId, 'branch_id' => null, 'key' => $key],
                ['value' => ['data' => $value], 'value_type' => is_bool($value) ? 'boolean' : 'integer'],
            );
        }

        if ($branchId) {
            SystemSetting::query()->updateOrCreate(
                ['company_id' => $companyId, 'branch_id' => $branchId, 'key' => 'branch_display_name'],
                ['value' => ['data' => 'Head Office'], 'value_type' => 'string'],
            );
        }
    }

    protected function seedNumberingSequences(int $companyId, ?int $branchId): void
    {
        $template = config('platform.numbering.default_template');

        foreach (DocumentType::cases() as $documentType) {
            NumberingSequence::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'document_type' => $documentType->value,
                ],
                [
                    'format_template' => $template,
                    'next_number' => 1,
                    'padding' => config('platform.numbering.default_padding', 5),
                    'include_year' => true,
                    'include_branch_code' => true,
                ],
            );
        }
    }

    protected function seedFormSettings(int $companyId): void
    {
        $manager = app(FormSettingsManager::class);
        $manager->ensureForms($companyId, null);

        $manager->save($companyId, null, [
            'customer' => [
                'is_active' => true,
                'fields' => [
                    'kra_pin' => ['visibility' => 'visible', 'requirement' => 'required', 'read_only' => '0', 'default_value' => ''],
                    'website' => ['visibility' => 'hidden', 'requirement' => 'optional', 'read_only' => '0', 'default_value' => ''],
                ],
            ],
            'lead' => [
                'is_active' => true,
                'fields' => [
                    'estimated_value' => ['visibility' => 'visible', 'requirement' => 'required', 'read_only' => '0', 'default_value' => ''],
                ],
            ],
            'quotation' => [
                'is_active' => true,
                'fields' => [
                    'customer_id' => ['visibility' => 'visible', 'requirement' => 'required', 'read_only' => '0', 'default_value' => ''],
                    'lead_id' => ['visibility' => 'visible', 'requirement' => 'optional', 'read_only' => '0', 'default_value' => ''],
                    'valid_until' => ['visibility' => 'visible', 'requirement' => 'optional', 'read_only' => '0', 'default_value' => ''],
                    'currency' => ['visibility' => 'visible', 'requirement' => 'required', 'read_only' => '0', 'default_value' => 'KES'],
                    'notes' => ['visibility' => 'visible', 'requirement' => 'optional', 'read_only' => '0', 'default_value' => ''],
                ],
            ],
            'artwork' => [
                'is_active' => true,
                'fields' => [
                    'due_date' => ['visibility' => 'visible', 'requirement' => 'required', 'read_only' => '0', 'default_value' => ''],
                ],
            ],
        ]);
    }

    protected function seedDocumentTypes(int $companyId, ?int $branchId): void
    {
        app(DocumentTypesManager::class)->ensureDefinitions($companyId, $branchId);
    }

    protected function seedApprovalRules(int $companyId, ?int $branchId): void
    {
        $definitions = [
            ApprovalRuleType::QuotationApproval->value => [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => 50000, 'threshold_percent' => null, 'approver_role' => 'Branch Manager', 'approver_permission' => 'quotations.approve'],
                    ['threshold_amount' => 100000, 'threshold_percent' => null, 'approver_role' => 'Company Admin', 'approver_permission' => 'quotations.approve'],
                    ['threshold_amount' => 500000, 'threshold_percent' => null, 'approver_role' => 'Company Admin', 'approver_permission' => 'quotations.approve'],
                ],
            ],
            ApprovalRuleType::DiscountApproval->value => [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => null, 'threshold_percent' => 5, 'approver_role' => 'Sales', 'approver_permission' => 'quotations.approve'],
                    ['threshold_amount' => null, 'threshold_percent' => 10, 'approver_role' => 'Branch Manager', 'approver_permission' => 'quotations.approve'],
                    ['threshold_amount' => null, 'threshold_percent' => 20, 'approver_role' => 'Company Admin', 'approver_permission' => 'quotations.approve'],
                ],
            ],
            ApprovalRuleType::StockAdjustmentApproval->value => [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => 1000, 'threshold_percent' => null, 'approver_role' => 'Storekeeper', 'approver_permission' => 'inventory.adjust'],
                ],
            ],
            ApprovalRuleType::ProcurementApproval->value => [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => 50000, 'threshold_percent' => null, 'approver_role' => 'Company Admin', 'approver_permission' => null],
                ],
            ],
            ApprovalRuleType::PaymentApproval->value => [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => 100000, 'threshold_percent' => null, 'approver_role' => 'Company Admin', 'approver_permission' => null],
                ],
            ],
        ];

        foreach ($definitions as $ruleType => $definition) {
            $firstTier = $definition['tiers'][0] ?? null;

            ApprovalRule::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'rule_type' => $ruleType,
                ],
                [
                    'is_enabled' => $definition['is_enabled'],
                    'min_approvers' => $definition['min_approvers'],
                    'threshold_amount' => $firstTier['threshold_amount'] ?? null,
                    'threshold_percent' => $firstTier['threshold_percent'] ?? null,
                    'approver_role' => $firstTier['approver_role'] ?? null,
                    'settings_json' => ['tiers' => $definition['tiers']],
                ],
            );
        }
    }

    protected function seedApprovalChains(int $companyId, ?int $branchId): void
    {
        $definitions = [
            [
                'name' => 'Discount Approval Chain',
                'module' => 'sales',
                'document_type' => 'discount',
                'approval_rule_type' => ApprovalRuleType::DiscountApproval,
                'approval_mode' => ApprovalChainMode::Sequential,
                'steps' => [
                    ['step_number' => 1, 'approver_role' => 'Sales', 'is_required' => true],
                    ['step_number' => 2, 'approver_role' => 'Branch Manager', 'is_required' => true],
                    ['step_number' => 3, 'approver_role' => 'Company Admin', 'is_required' => true],
                ],
            ],
            [
                'name' => 'Inventory Adjustment Chain',
                'module' => 'inventory',
                'document_type' => 'stock_adjustment',
                'approval_rule_type' => ApprovalRuleType::StockAdjustmentApproval,
                'approval_mode' => ApprovalChainMode::Sequential,
                'steps' => [
                    ['step_number' => 1, 'approver_role' => 'Storekeeper', 'is_required' => true],
                ],
            ],
            [
                'name' => 'Purchase Order Chain',
                'module' => 'procurement',
                'document_type' => 'purchase_order',
                'approval_rule_type' => ApprovalRuleType::ProcurementApproval,
                'approval_mode' => ApprovalChainMode::Sequential,
                'steps' => [
                    ['step_number' => 1, 'approver_role' => 'Company Admin', 'is_required' => true],
                    ['step_number' => 2, 'approver_role' => 'Company Admin', 'is_required' => true],
                    ['step_number' => 3, 'approver_role' => 'Company Admin', 'is_required' => true],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $chain = ApprovalChain::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'name' => $definition['name'],
                ],
                [
                    'module' => $definition['module'],
                    'document_type' => $definition['document_type'],
                    'approval_rule_type' => $definition['approval_rule_type'],
                    'approval_mode' => $definition['approval_mode'],
                    'status' => ApprovalChainStatus::Active,
                    'description' => __('Seeded default approval chain.'),
                ],
            );

            $chain->steps()->delete();

            foreach ($definition['steps'] as $step) {
                ApprovalChainStep::query()->create([
                    'approval_chain_id' => $chain->id,
                    'step_number' => $step['step_number'],
                    'approver_role' => $step['approver_role'],
                    'is_required' => $step['is_required'],
                ]);
            }
        }
    }

    protected function seedEscalationRules(int $companyId, ?int $branchId): void
    {
        Role::findOrCreate('Finance Director', 'web');
        Role::findOrCreate('Operations Manager', 'web');

        $definitions = [
            [
                'name' => 'Purchase Order SLA',
                'workflow_key' => 'purchase_order',
                'waiting_hours' => 48,
                'escalation_target_role' => 'Finance Director',
                'escalation_method' => EscalationMethod::AutoEscalate,
            ],
            [
                'name' => 'Inventory Adjustment SLA',
                'workflow_key' => 'inventory_adjustment',
                'waiting_hours' => 24,
                'escalation_target_role' => 'Operations Manager',
                'escalation_method' => EscalationMethod::AutoEscalate,
            ],
        ];

        foreach ($definitions as $definition) {
            WorkflowEscalationRule::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'name' => $definition['name'],
                ],
                [
                    'workflow_key' => $definition['workflow_key'],
                    'waiting_hours' => $definition['waiting_hours'],
                    'escalation_target_role' => $definition['escalation_target_role'],
                    'escalation_method' => $definition['escalation_method'],
                    'status' => EscalationRuleStatus::Active,
                    'description' => __('Seeded default escalation rule.'),
                ],
            );
        }
    }

    protected function seedWorkflowRules(int $companyId, ?int $branchId): void
    {
        $definitions = [
            [
                'name' => 'Quotation Approved → Sales Order',
                'module' => 'commercial',
                'entity_type' => 'quotation',
                'trigger' => WorkflowRuleTrigger::Approved,
                'description' => __('Automatically create a sales order when a quotation is approved.'),
                'actions' => [
                    [
                        'sort_order' => 1,
                        'action_type' => WorkflowRuleActionType::CreateDocument,
                        'config_json' => ['target_entity' => 'sales_order'],
                    ],
                ],
            ],
            [
                'name' => 'Job Card Completed → Notify Sales',
                'module' => 'production',
                'entity_type' => 'production_job_card',
                'trigger' => WorkflowRuleTrigger::Completed,
                'description' => __('Notify sales when production job card is completed.'),
                'actions' => [
                    [
                        'sort_order' => 1,
                        'action_type' => WorkflowRuleActionType::SendNotification,
                        'config_json' => [
                            'recipient_role' => 'Sales',
                            'notification_type' => 'production_completed',
                            'title' => 'Production completed',
                            'body' => 'A production job card has been completed.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $actions = $definition['actions'];
            unset($definition['actions']);

            $rule = WorkflowRule::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'name' => $definition['name'],
                ],
                [
                    ...$definition,
                    'status' => WorkflowRuleStatus::Active,
                ],
            );

            $rule->actions()->delete();

            foreach ($actions as $action) {
                WorkflowRuleAction::query()->create([
                    'workflow_rule_id' => $rule->id,
                    ...$action,
                ]);
            }
        }
    }
}
