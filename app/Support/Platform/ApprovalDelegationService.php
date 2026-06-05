<?php

namespace App\Support\Platform;

use App\Enums\ApprovalRuleType;
use App\Enums\DelegationStatus;
use App\Models\Platform\ApprovalDelegation;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ApprovalDelegationService
{
    public function __construct(
        protected SecurityAuditService $auditService,
    ) {}

    public function syncStatuses(?int $companyId = null): int
    {
        $today = now()->startOfDay();
        $updated = 0;

        $query = ApprovalDelegation::query()
            ->whereIn('status', [DelegationStatus::Scheduled, DelegationStatus::Active]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        foreach ($query->get() as $delegation) {
            $nextStatus = $this->resolveStatusForDates($delegation->start_date, $delegation->end_date, $today);

            if ($delegation->status !== $nextStatus) {
                $delegation->update(['status' => $nextStatus]);
                $updated++;
            }
        }

        return $updated;
    }

    public function canActAsDelegate(
        User $actor,
        string $approvalType,
        string $module,
        int $companyId,
        ?int $branchId,
        string $requiredPermission,
    ): bool {
        return $this->matchingDelegations(
            $actor,
            $approvalType,
            $module,
            $companyId,
            $branchId,
            $requiredPermission,
        )->isNotEmpty();
    }

    /**
     * @return Collection<int, ApprovalDelegation>
     */
    public function matchingDelegations(
        User $actor,
        string $approvalType,
        string $module,
        int $companyId,
        ?int $branchId,
        string $requiredPermission,
    ): Collection {
        $this->syncStatuses($companyId);

        return ApprovalDelegation::query()
            ->with('delegator')
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where(function ($scoped) use ($branchId) {
                $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->where('delegate_user_id', $actor->id)
            ->where('status', DelegationStatus::Active)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->get()
            ->filter(function (ApprovalDelegation $delegation) use ($approvalType, $module, $requiredPermission) {
                if (! $delegation->coversModule($module) || ! $delegation->coversApprovalType($approvalType)) {
                    return false;
                }

                $delegator = $delegation->delegator;

                return $delegator
                    && $delegator->is_active
                    && $delegator->can($requiredPermission);
            })
            ->values();
    }

    public function resolveActiveDelegation(
        User $actor,
        string $approvalType,
        string $module,
        int $companyId,
        ?int $branchId,
        string $requiredPermission,
    ): ?ApprovalDelegation {
        return $this->matchingDelegations(
            $actor,
            $approvalType,
            $module,
            $companyId,
            $branchId,
            $requiredPermission,
        )->first();
    }

    public function recordDelegatedApproval(
        User $actor,
        Model $subject,
        ApprovalDelegation $delegation,
        string $action,
        ?string $module = null,
    ): void {
        $this->auditService->record(
            action: $action,
            subject: $subject,
            description: __('Delegated approval by :delegate on behalf of :delegator', [
                'delegate' => $actor->name,
                'delegator' => $delegation->delegator?->name ?? __('Unknown'),
            ]),
            module: $module ?? 'governance',
            entity: 'approval_delegation',
            metadata: [
                'delegation_id' => $delegation->id,
                'delegator_user_id' => $delegation->delegator_user_id,
                'delegate_user_id' => $delegation->delegate_user_id,
                'approval_types' => $delegation->approval_types,
                'modules' => $delegation->modules,
                'reason' => $delegation->reason->value,
            ],
        );
    }

    public function resolveStatusForDates(Carbon $startDate, Carbon $endDate, ?Carbon $today = null): DelegationStatus
    {
        $today ??= now()->startOfDay();
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($today->gt($end)) {
            return DelegationStatus::Expired;
        }

        if ($today->lt($start)) {
            return DelegationStatus::Scheduled;
        }

        return DelegationStatus::Active;
    }

    /**
     * @return list<string>
     */
    public function moduleOptions(): array
    {
        return collect(config('delegation_registry.modules', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label'] ?? $key])
            ->all();
    }

    /**
     * @return list<string>
     */
    public function approvalTypeOptions(): array
    {
        return collect(config('approval_registry.rule_types', []))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label'] ?? $key])
            ->all();
    }
}
