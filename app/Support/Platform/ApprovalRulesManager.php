<?php

namespace App\Support\Platform;

use App\Enums\ApprovalRuleType;
use App\Models\Platform\ApprovalRule;
use Illuminate\Support\Collection;

class ApprovalRulesManager
{
    public function __construct(
        protected ApprovalRulesService $rules,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(int $companyId, ?int $branchId): Collection
    {
        $this->ensureRules($companyId, $branchId);

        return collect(config('approval_registry.rule_types', []))
            ->map(function (array $meta, string $slug) use ($companyId, $branchId) {
                $type = ApprovalRuleType::from($slug);
                $rule = $this->findRuleRecord($companyId, $branchId, $slug);
                $companyRule = $branchId ? $this->findRuleRecord($companyId, null, $slug) : null;

                return [
                    'rule_type' => $slug,
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'metric' => $meta['metric'],
                    'default_permission' => $meta['default_permission'],
                    'example_tiers' => $meta['example_tiers'] ?? [],
                    'is_enabled' => (bool) $rule?->is_enabled,
                    'min_approvers' => (int) ($rule?->min_approvers ?? 1),
                    'tiers' => $rule ? $this->rules->tiersForRule($rule) : [],
                    'inherits_company' => $branchId && ! $rule && $companyRule,
                    'company_tiers' => $companyRule ? $this->rules->tiersForRule($companyRule) : [],
                ];
            })
            ->values();
    }

    /**
     * @param  array<string, array<string, mixed>>  $payload
     */
    public function save(int $companyId, ?int $branchId, array $payload): void
    {
        foreach (config('approval_registry.rule_types', []) as $slug => $meta) {
            if (! isset($payload[$slug])) {
                continue;
            }

            $input = $payload[$slug];
            $tiers = collect($input['tiers'] ?? [])
                ->filter(fn (array $tier) => $this->tierHasThreshold($tier))
                ->map(fn (array $tier) => $this->normalizeTier($tier, $meta))
                ->values()
                ->all();

            $firstTier = $tiers[0] ?? null;

            ApprovalRule::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'rule_type' => $slug,
                ],
                [
                    'is_enabled' => filter_var($input['is_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'min_approvers' => max(1, (int) ($input['min_approvers'] ?? 1)),
                    'threshold_amount' => $firstTier['threshold_amount'] ?? null,
                    'threshold_percent' => $firstTier['threshold_percent'] ?? null,
                    'approver_role' => $firstTier['approver_role'] ?? null,
                    'settings_json' => ['tiers' => $tiers],
                ],
            );
        }
    }

    public function ensureRules(int $companyId, ?int $branchId): void
    {
        foreach (array_keys(config('approval_registry.rule_types', [])) as $slug) {
            ApprovalRule::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'rule_type' => $slug,
                ],
                [
                    'is_enabled' => false,
                    'min_approvers' => 1,
                    'settings_json' => ['tiers' => []],
                ],
            );
        }
    }

    protected function findRuleRecord(int $companyId, ?int $branchId, string $ruleType): ?ApprovalRule
    {
        return ApprovalRule::query()
            ->where('company_id', $companyId)
            ->where('rule_type', $ruleType)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->first();
    }

    /**
     * @param  array<string, mixed>  $tier
     */
    protected function tierHasThreshold(array $tier): bool
    {
        return filled($tier['threshold_amount'] ?? null) || filled($tier['threshold_percent'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $tier
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function normalizeTier(array $tier, array $meta): array
    {
        return [
            'threshold_amount' => filled($tier['threshold_amount'] ?? null)
                ? (float) $tier['threshold_amount']
                : null,
            'threshold_percent' => filled($tier['threshold_percent'] ?? null)
                ? (float) $tier['threshold_percent']
                : null,
            'approver_role' => filled($tier['approver_role'] ?? null) ? (string) $tier['approver_role'] : null,
            'approver_permission' => filled($tier['approver_permission'] ?? null)
                ? (string) $tier['approver_permission']
                : ($meta['default_permission'] ?? null),
            'min_approvers' => max(1, (int) ($tier['min_approvers'] ?? 1)),
        ];
    }
}
