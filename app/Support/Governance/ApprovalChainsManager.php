<?php

namespace App\Support\Governance;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Governance\ApprovalChain;
use App\Models\Governance\ApprovalChainStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalChainsManager
{
    /**
     * @return Collection<int, ApprovalChain>
     */
    public function chainsForScope(int $companyId, ?int $branchId): Collection
    {
        return ApprovalChain::query()
            ->where('company_id', $companyId)
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->with(['steps.approverUser'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $steps
     */
    public function create(int $companyId, ?int $branchId, array $data, array $steps, User $actor): ApprovalChain
    {
        return DB::transaction(function () use ($companyId, $branchId, $data, $steps, $actor) {
            $chain = ApprovalChain::query()->create([
                ...$this->chainAttributes($data),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'status' => ApprovalChainStatus::Draft,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->syncSteps($chain, $steps);

            return $chain->load('steps.approverUser');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $steps
     */
    public function update(ApprovalChain $chain, array $data, array $steps, User $actor): ApprovalChain
    {
        return DB::transaction(function () use ($chain, $data, $steps, $actor) {
            $chain->update([
                ...$this->chainAttributes($data),
                'updated_by' => $actor->id,
            ]);

            $this->syncSteps($chain, $steps);

            return $chain->fresh(['steps.approverUser']);
        });
    }

    public function activate(ApprovalChain $chain, User $actor): ApprovalChain
    {
        if ($chain->steps()->count() === 0) {
            throw ValidationException::withMessages([
                'steps' => __('An approval chain must have at least one step before activation.'),
            ]);
        }

        ApprovalChain::query()
            ->where('company_id', $chain->company_id)
            ->where('branch_id', $chain->branch_id)
            ->where('approval_rule_type', $chain->approval_rule_type)
            ->where('id', '!=', $chain->id)
            ->where('status', ApprovalChainStatus::Active)
            ->update(['status' => ApprovalChainStatus::Inactive]);

        $chain->update([
            'status' => ApprovalChainStatus::Active,
            'updated_by' => $actor->id,
        ]);

        return $chain->fresh(['steps.approverUser']);
    }

    public function deactivate(ApprovalChain $chain, User $actor): ApprovalChain
    {
        $chain->update([
            'status' => ApprovalChainStatus::Inactive,
            'updated_by' => $actor->id,
        ]);

        return $chain->fresh(['steps.approverUser']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function chainAttributes(array $data): array
    {
        return [
            'name' => (string) $data['name'],
            'module' => (string) $data['module'],
            'document_type' => filled($data['document_type'] ?? null) ? (string) $data['document_type'] : null,
            'approval_rule_type' => (string) $data['approval_rule_type'],
            'approval_mode' => (string) $data['approval_mode'],
            'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
            'condition_json' => $this->normalizeCondition($data),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, float>|null
     */
    protected function normalizeCondition(array $data): ?array
    {
        $condition = array_filter([
            'min_amount' => filled($data['min_amount'] ?? null) ? (float) $data['min_amount'] : null,
            'max_amount' => filled($data['max_amount'] ?? null) ? (float) $data['max_amount'] : null,
            'min_percent' => filled($data['min_percent'] ?? null) ? (float) $data['min_percent'] : null,
            'max_percent' => filled($data['max_percent'] ?? null) ? (float) $data['max_percent'] : null,
        ], fn ($value) => $value !== null);

        return $condition === [] ? null : $condition;
    }

    /**
     * @param  list<array<string, mixed>>  $steps
     */
    protected function syncSteps(ApprovalChain $chain, array $steps): void
    {
        $chain->steps()->delete();

        foreach (array_values($steps) as $index => $step) {
            if (! filled($step['approver_role'] ?? null) && ! filled($step['approver_user_id'] ?? null)) {
                continue;
            }

            ApprovalChainStep::query()->create([
                'approval_chain_id' => $chain->id,
                'step_number' => $index + 1,
                'approver_role' => filled($step['approver_role'] ?? null) ? (string) $step['approver_role'] : null,
                'approver_user_id' => filled($step['approver_user_id'] ?? null) ? (int) $step['approver_user_id'] : null,
                'approval_limit' => filled($step['approval_limit'] ?? null) ? (float) $step['approval_limit'] : null,
                'is_required' => filter_var($step['is_required'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'condition_json' => $this->normalizeStepCondition($step),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $step
     * @return array<string, float>|null
     */
    protected function normalizeStepCondition(array $step): ?array
    {
        $condition = array_filter([
            'min_amount' => filled($step['min_amount'] ?? null) ? (float) $step['min_amount'] : null,
            'max_amount' => filled($step['max_amount'] ?? null) ? (float) $step['max_amount'] : null,
            'min_percent' => filled($step['min_percent'] ?? null) ? (float) $step['min_percent'] : null,
            'max_percent' => filled($step['max_percent'] ?? null) ? (float) $step['max_percent'] : null,
        ], fn ($value) => $value !== null);

        return $condition === [] ? null : $condition;
    }

    /**
     * @return list<string>
     */
    public function moduleOptions(): array
    {
        return collect(config('chain_registry.modules', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => __($meta['label'])])
            ->all();
    }

    /**
     * @return list<string>
     */
    public function ruleTypeOptions(): array
    {
        return collect(config('approval_registry.rule_types', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => __($meta['label'])])
            ->all();
    }

    public function defaultModuleForRuleType(string $ruleType): ?string
    {
        foreach (config('chain_registry.modules', []) as $module => $meta) {
            if (in_array($ruleType, $meta['rule_types'] ?? [], true)) {
                return $module;
            }
        }

        return null;
    }

    public function defaultDocumentTypeForRuleType(string $ruleType): ?string
    {
        foreach (config('chain_registry.document_types', []) as $slug => $meta) {
            if (($meta['rule_type'] ?? null) === $ruleType) {
                return $slug;
            }
        }

        return null;
    }

    public function defaultModeForRuleType(ApprovalRuleType $ruleType): ApprovalChainMode
    {
        return match ($ruleType) {
            ApprovalRuleType::DiscountApproval,
            ApprovalRuleType::ProcurementApproval => ApprovalChainMode::Sequential,
            ApprovalRuleType::StockAdjustmentApproval => ApprovalChainMode::Sequential,
            default => ApprovalChainMode::Sequential,
        };
    }
}
