<?php

namespace App\Support\Platform;

use App\Enums\ApprovalRuleType;
use App\Models\Platform\ApprovalRule;

class ApprovalRulesService
{
    /**
     * @return array{rule: ApprovalRule, scope: 'branch'|'company'}|null
     */
    public function resolveWithScope(
        ApprovalRuleType $ruleType,
        ?int $companyId = null,
        ?int $branchId = null,
    ): ?array {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        if ($branchId !== null) {
            $branchRule = $this->findRule($ruleType, $companyId, $branchId);
            if ($branchRule && $this->hasConfiguredTiers($branchRule)) {
                return ['rule' => $branchRule, 'scope' => 'branch'];
            }
        }

        $companyRule = $this->findRule($ruleType, $companyId, null);
        if ($companyRule) {
            return ['rule' => $companyRule, 'scope' => 'company'];
        }

        if ($branchId !== null && isset($branchRule) && $branchRule) {
            return ['rule' => $branchRule, 'scope' => 'branch'];
        }

        return null;
    }

    public function requiresApproval(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): bool {
        return $this->evaluate($ruleType, $amount, $percent, $companyId, $branchId)['requires_approval'];
    }

    public function approverRole(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): ?string {
        return $this->evaluate($ruleType, $amount, $percent, $companyId, $branchId)['approver_role'];
    }

    public function approverPermission(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): ?string {
        return $this->evaluate($ruleType, $amount, $percent, $companyId, $branchId)['approver_permission'];
    }

    /**
     * @return array{
     *     requires_approval: bool,
     *     rule: ApprovalRule|null,
     *     tier: array<string, mixed>|null,
     *     approver_role: string|null,
     *     approver_permission: string|null,
     *     min_approvers: int,
     *     scope: 'branch'|'company'|null,
     * }
     */
    public function evaluate(
        ApprovalRuleType $ruleType,
        ?float $amount = null,
        ?float $percent = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        $resolved = $this->resolveWithScope($ruleType, $companyId, $branchId);

        if (! $resolved) {
            return $this->emptyEvaluation();
        }

        ['rule' => $rule, 'scope' => $scope] = $resolved;

        if (! $rule->is_enabled) {
            return $this->emptyEvaluation($rule, $scope);
        }

        $tier = $this->matchingTier($rule, $ruleType, $amount, $percent);

        if (! $tier) {
            return [
                'requires_approval' => false,
                'rule' => $rule,
                'tier' => null,
                'approver_role' => null,
                'approver_permission' => null,
                'min_approvers' => (int) $rule->min_approvers,
                'scope' => $scope,
            ];
        }

        return [
            'requires_approval' => true,
            'rule' => $rule,
            'tier' => $tier,
            'approver_role' => $tier['approver_role'] ?? $rule->approver_role,
            'approver_permission' => $tier['approver_permission']
                ?? config("approval_registry.rule_types.{$ruleType->value}.default_permission"),
            'min_approvers' => (int) ($tier['min_approvers'] ?? $rule->min_approvers),
            'scope' => $scope,
        ];
    }

    /**
     * @return array{requires_approval: bool, rule: ApprovalRule|null, tier: null, approver_role: null, approver_permission: null, min_approvers: int, scope: string|null}
     */
    protected function emptyEvaluation(?ApprovalRule $rule = null, ?string $scope = null): array
    {
        return [
            'requires_approval' => false,
            'rule' => $rule,
            'tier' => null,
            'approver_role' => null,
            'approver_permission' => null,
            'min_approvers' => (int) ($rule?->min_approvers ?? 1),
            'scope' => $scope,
        ];
    }

    public function rule(
        ApprovalRuleType $ruleType,
        ?int $companyId = null,
        ?int $branchId = null,
    ): ?ApprovalRule {
        return $this->resolveWithScope($ruleType, $companyId, $branchId)['rule'] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tiersForRule(ApprovalRule $rule): array
    {
        $stored = $rule->settings_json['tiers'] ?? [];

        if ($stored !== []) {
            return array_values($stored);
        }

        if ($rule->threshold_amount !== null || $rule->threshold_percent !== null || $rule->approver_role) {
            return [[
                'threshold_amount' => $rule->threshold_amount !== null ? (float) $rule->threshold_amount : null,
                'threshold_percent' => $rule->threshold_percent !== null ? (float) $rule->threshold_percent : null,
                'approver_role' => $rule->approver_role,
                'approver_permission' => config("approval_registry.rule_types.{$rule->rule_type}.default_permission"),
                'min_approvers' => (int) $rule->min_approvers,
            ]];
        }

        return [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function matchingTier(
        ApprovalRule $rule,
        ApprovalRuleType $ruleType,
        ?float $amount,
        ?float $percent,
    ): ?array {
        $metric = config("approval_registry.rule_types.{$ruleType->value}.metric", 'amount');
        $matches = collect($this->tiersForRule($rule))
            ->filter(function (array $tier) use ($metric, $amount, $percent) {
                if ($metric === 'amount') {
                    return $amount !== null
                        && $tier['threshold_amount'] !== null
                        && $amount >= (float) $tier['threshold_amount'];
                }

                if ($metric === 'percent') {
                    return $percent !== null
                        && $tier['threshold_percent'] !== null
                        && $percent >= (float) $tier['threshold_percent'];
                }

                return false;
            });

        if ($matches->isEmpty()) {
            return null;
        }

        return $matches->sortByDesc(function (array $tier) use ($metric) {
            return $metric === 'percent'
                ? (float) ($tier['threshold_percent'] ?? 0)
                : (float) ($tier['threshold_amount'] ?? 0);
        })->first();
    }

    protected function hasConfiguredTiers(ApprovalRule $rule): bool
    {
        return $this->tiersForRule($rule) !== []
            || $rule->threshold_amount !== null
            || $rule->threshold_percent !== null;
    }

    protected function findRule(ApprovalRuleType $ruleType, ?int $companyId, ?int $branchId): ?ApprovalRule
    {
        return ApprovalRule::query()
            ->where('rule_type', $ruleType->value)
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->first();
    }
}
