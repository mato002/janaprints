<?php

namespace Database\Seeders;

use App\Enums\ApprovalRuleType;
use App\Enums\DocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\NumberingSequence;
use App\Models\Platform\SystemSetting;
use App\Support\Platform\FormSettingsManager;
use Illuminate\Database\Seeder;

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
    }

    protected function seedSystemSettings(int $companyId, ?int $branchId): void
    {
        $defaults = [
            'quotation_validity_days' => 30,
            'default_payment_terms' => 'Net 30',
            'default_tax_rate' => 16,
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
}
