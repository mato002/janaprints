<?php

namespace App\Support\Governance;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalChainStepStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Governance\ApprovalChainRun;
use App\Models\User;
use App\Support\Platform\ApprovalDelegationService;
use App\Support\Platform\ApprovalRulesService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ApprovalEnforcementEngine
{
    public function __construct(
        protected ApprovalRulesService $rules,
        protected ApprovalChainsService $chains,
        protected ApprovalChainEngine $chainEngine,
        protected ApprovalDelegationService $delegations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function evaluate(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        return $this->rules->evaluate($ruleType, $amount, $percent, $companyId, $branchId);
    }

    public function requiresApproval(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): bool {
        return $this->rules->requiresApproval($ruleType, $amount, $percent, $companyId, $branchId);
    }

    /**
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    public function beginApproval(
        Model $subject,
        ApprovalRuleType $ruleType,
        array $context = [],
        ?int $companyId = null,
        ?int $branchId = null,
    ): ?ApprovalChainRun {
        $evaluation = $this->evaluate(
            $ruleType,
            $context['amount'] ?? null,
            $context['percent'] ?? null,
            $companyId ?? (int) $subject->getAttribute('company_id'),
            $branchId ?? $subject->getAttribute('branch_id'),
        );

        if (! $evaluation['requires_approval'] || $evaluation['approval_chain'] === null) {
            return null;
        }

        $existing = $this->latestRun($subject);

        if ($existing && $existing->status === ApprovalChainRunStatus::Pending) {
            return $existing;
        }

        return $this->chains->startRun(
            $evaluation['approval_chain'],
            $subject,
            $context,
            $companyId ?? (int) $subject->getAttribute('company_id'),
            $branchId ?? $subject->getAttribute('branch_id'),
        );
    }

    /**
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    public function assertPostingAllowed(
        Model $subject,
        ApprovalRuleType $ruleType,
        bool $documentApproved,
        array $context = [],
        ?int $companyId = null,
        ?int $branchId = null,
    ): void {
        $evaluation = $this->evaluate(
            $ruleType,
            $context['amount'] ?? null,
            $context['percent'] ?? null,
            $companyId ?? (int) $subject->getAttribute('company_id'),
            $branchId ?? $subject->getAttribute('branch_id'),
        );

        if (! $evaluation['requires_approval']) {
            return;
        }

        if (! $documentApproved) {
            throw ValidationException::withMessages([
                'approval' => __('Document must be approved before posting.'),
            ]);
        }

        if ($evaluation['approval_chain'] !== null && ! $this->hasApprovedChain($subject)) {
            throw ValidationException::withMessages([
                'approval' => __('Approval chain must be completed before posting.'),
            ]);
        }
    }

    /**
     * Gate posting for documents without a separate approved status (journals, payments).
     *
     * @param  array{amount?: float|null, percent?: float|null}  $context
     */
    public function assertChainApprovedForPosting(
        Model $subject,
        ApprovalRuleType $ruleType,
        array $context = [],
        ?int $companyId = null,
        ?int $branchId = null,
    ): void {
        $evaluation = $this->evaluate(
            $ruleType,
            $context['amount'] ?? null,
            $context['percent'] ?? null,
            $companyId ?? (int) $subject->getAttribute('company_id'),
            $branchId ?? $subject->getAttribute('branch_id'),
        );

        if (! $evaluation['requires_approval']) {
            return;
        }

        $run = $this->latestRun($subject);

        if ($run === null && $evaluation['approval_chain'] !== null) {
            $this->beginApproval($subject, $ruleType, $context, $companyId, $branchId);

            throw ValidationException::withMessages([
                'approval' => __('Approval is required before posting. The document has been submitted for approval.'),
            ]);
        }

        if (! $this->hasApprovedChain($subject)) {
            throw ValidationException::withMessages([
                'approval' => __('Approval chain must be completed before posting.'),
            ]);
        }
    }

    public function hasApprovedChain(Model $subject): bool
    {
        $run = $this->latestRun($subject);

        if ($run === null) {
            return true;
        }

        return $run->status === ApprovalChainRunStatus::Approved;
    }

    public function latestRun(Model $subject): ?ApprovalChainRun
    {
        return ApprovalChainRun::query()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->latest('id')
            ->first();
    }

    public function recordApproval(Model $subject, User $actor, ?string $notes = null): ?ApprovalChainRun
    {
        $run = $this->latestRun($subject);

        if ($run === null || $run->status !== ApprovalChainRunStatus::Pending) {
            return $run;
        }

        $permission = config("approval_registry.rule_types.{$run->approval_rule_type->value}.default_permission");

        foreach ($this->chainEngine->actionableStepRecords($run) as $record) {
            if ($this->canActOnStep($actor, $record, $run->approval_rule_type, $permission, $subject)) {
                return $this->chains->recordStepAction(
                    $record,
                    ApprovalChainStepStatus::Approved,
                    $actor,
                    $notes,
                );
            }
        }

        throw ValidationException::withMessages([
            'approval' => __('You are not authorized to approve the current step.'),
        ]);
    }

    public function recordRejection(Model $subject, User $actor, ?string $notes = null): ?ApprovalChainRun
    {
        $run = $this->latestRun($subject);

        if ($run === null || $run->status !== ApprovalChainRunStatus::Pending) {
            return $run;
        }

        $permission = config("approval_registry.rule_types.{$run->approval_rule_type->value}.default_permission");

        foreach ($this->chainEngine->actionableStepRecords($run) as $record) {
            if ($this->canActOnStep($actor, $record, $run->approval_rule_type, $permission, $subject)) {
                return $this->chains->recordStepAction(
                    $record,
                    ApprovalChainStepStatus::Rejected,
                    $actor,
                    $notes,
                );
            }
        }

        throw ValidationException::withMessages([
            'approval' => __('You are not authorized to reject the current step.'),
        ]);
    }

    protected function canActOnStep(
        User $actor,
        \App\Models\Governance\ApprovalChainStepRecord $record,
        ApprovalRuleType $ruleType,
        ?string $permission,
        Model $subject,
    ): bool {
        if ($this->chainEngine->canUserActOnStep($actor, $record)) {
            return true;
        }

        if ($permission && $actor->can($permission)) {
            return true;
        }

        $module = config("chain_registry.document_types.{$this->documentTypeKey($ruleType)}.module", 'finance');

        return $this->delegations->canActAsDelegate(
            $actor,
            $ruleType->value,
            $module,
            (int) $subject->getAttribute('company_id'),
            $subject->getAttribute('branch_id'),
            $permission ?? 'settings.view',
        );
    }

    protected function documentTypeKey(ApprovalRuleType $ruleType): string
    {
        return match ($ruleType) {
            ApprovalRuleType::QuotationApproval => 'quotation',
            ApprovalRuleType::DiscountApproval => 'discount',
            ApprovalRuleType::StockAdjustmentApproval => 'stock_adjustment',
            ApprovalRuleType::PurchaseRequestApproval => 'purchase_request',
            ApprovalRuleType::ProcurementApproval => 'purchase_order',
            ApprovalRuleType::RfqApproval => 'rfq',
            ApprovalRuleType::GoodsReceiptApproval => 'goods_receipt',
            ApprovalRuleType::VendorInvoiceApproval => 'vendor_invoice',
            ApprovalRuleType::PaymentApproval => 'payment',
            ApprovalRuleType::PayrollApproval => 'payroll_run',
            ApprovalRuleType::AssetCapitalizationApproval => 'asset_capitalization',
            default => 'payment',
        };
    }
}
