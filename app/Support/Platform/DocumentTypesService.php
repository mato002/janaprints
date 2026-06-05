<?php

namespace App\Support\Platform;

use App\Enums\DocumentTypeStatus;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\DocumentTypeDefinition;
use App\Models\Platform\NumberingSequence;

class DocumentTypesService
{
    public function findByCode(int $companyId, ?int $branchId, string $code): ?DocumentTypeDefinition
    {
        return DocumentTypeDefinition::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('code', $code)
            ->first();
    }

    public function isActive(int $companyId, ?int $branchId, string $code): bool
    {
        $definition = $this->findByCode($companyId, $branchId, $code);

        return $definition?->isActive() ?? false;
    }

    public function resolveNumberSeries(
        int $companyId,
        ?int $branchId,
        string $code,
    ): ?NumberingSequence {
        $definition = $this->findByCode($companyId, $branchId, $code);

        if (! $definition?->number_series_key || ! $definition->auto_numbering || ! $definition->isActive()) {
            return null;
        }

        return NumberingSequence::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('document_type', $definition->number_series_key)
            ->first();
    }

    public function resolveApprovalRule(
        int $companyId,
        ?int $branchId,
        string $code,
    ): ?ApprovalRule {
        $definition = $this->findByCode($companyId, $branchId, $code);

        if (! $definition?->approval_required || ! $definition->approval_rule_type || ! $definition->isActive()) {
            return null;
        }

        return $this->findApprovalRule($companyId, $branchId, $definition->approval_rule_type);
    }

    public function requiresApproval(int $companyId, ?int $branchId, string $code): bool
    {
        $definition = $this->findByCode($companyId, $branchId, $code);

        return $definition?->approval_required
            && $definition->isActive()
            && $definition->status === DocumentTypeStatus::Active;
    }

    public function auditTrackingEnabled(int $companyId, ?int $branchId, string $code): bool
    {
        $definition = $this->findByCode($companyId, $branchId, $code);

        return (bool) ($definition?->workflow_json['audit_tracking'] ?? true);
    }

    public function retentionPeriodDays(int $companyId, ?int $branchId, string $code): ?int
    {
        return $this->findByCode($companyId, $branchId, $code)?->retention_period_days;
    }

    protected function findApprovalRule(int $companyId, ?int $branchId, string $ruleType): ?ApprovalRule
    {
        $query = ApprovalRule::query()
            ->where('company_id', $companyId)
            ->where('rule_type', $ruleType)
            ->where('is_enabled', true);

        if ($branchId) {
            $branchRule = (clone $query)->where('branch_id', $branchId)->first();

            if ($branchRule) {
                return $branchRule;
            }
        }

        return $query->whereNull('branch_id')->first();
    }
}
