<?php

namespace App\Support\Platform;

use App\Enums\DelegationReason;
use App\Enums\DelegationStatus;
use App\Models\Platform\ApprovalDelegation;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ApprovalDelegationManager
{
    public function __construct(
        protected ApprovalDelegationService $service,
        protected SecurityAuditService $auditService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function dashboardRows(int $companyId, ?int $branchId): array
    {
        $this->service->syncStatuses($companyId);

        return ApprovalDelegation::query()
            ->with(['delegator:id,name,email', 'delegate:id,name,email', 'branch:id,name'])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where(function ($scoped) use ($branchId) {
                $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ApprovalDelegation $delegation) => $this->presentRow($delegation))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRow(ApprovalDelegation $delegation): array
    {
        return [
            'id' => $delegation->id,
            'delegator' => $delegation->delegator?->name,
            'delegator_id' => $delegation->delegator_user_id,
            'delegate' => $delegation->delegate?->name,
            'delegate_id' => $delegation->delegate_user_id,
            'modules' => $this->formatModules($delegation->modules ?? []),
            'approval_types' => $this->formatApprovalTypes($delegation->approval_types ?? []),
            'reason' => $delegation->reason->label(),
            'reason_key' => $delegation->reason->value,
            'start_date' => $delegation->start_date->format('Y-m-d'),
            'end_date' => $delegation->end_date->format('Y-m-d'),
            'status' => $delegation->status->label(),
            'status_key' => $delegation->status->value,
            'is_operational' => $delegation->status->isOperational(),
            'notes' => $delegation->notes,
            'branch' => $delegation->branch?->name,
        ];
    }

    public function create(int $companyId, ?int $branchId, array $data, User $actor): ApprovalDelegation
    {
        $this->assertDistinctUsers($data['delegator_user_id'], $data['delegate_user_id']);
        $this->assertNoOverlap($companyId, $branchId, $data);

        $status = $this->service->resolveStatusForDates(
            \Illuminate\Support\Carbon::parse($data['start_date']),
            \Illuminate\Support\Carbon::parse($data['end_date']),
        );

        $delegation = ApprovalDelegation::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'delegator_user_id' => $data['delegator_user_id'],
            'delegate_user_id' => $data['delegate_user_id'],
            'modules' => $data['modules'] ?? [],
            'approval_types' => $data['approval_types'] ?? [],
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => $actor->id,
        ]);

        $this->auditService->record(
            action: 'delegation.created',
            subject: $delegation,
            after: $this->auditSnapshot($delegation),
            module: 'governance',
            entity: 'approval_delegation',
        );

        return $delegation;
    }

    public function update(ApprovalDelegation $delegation, array $data, User $actor): ApprovalDelegation
    {
        if ($delegation->status === DelegationStatus::Cancelled) {
            throw new InvalidArgumentException(__('Cancelled delegations cannot be edited.'));
        }

        $this->assertDistinctUsers($data['delegator_user_id'], $data['delegate_user_id']);
        $this->assertNoOverlap(
            $delegation->company_id,
            $delegation->branch_id,
            $data,
            $delegation->id,
        );

        $before = $this->auditSnapshot($delegation);

        $delegation->fill([
            'delegator_user_id' => $data['delegator_user_id'],
            'delegate_user_id' => $data['delegate_user_id'],
            'modules' => $data['modules'] ?? [],
            'approval_types' => $data['approval_types'] ?? [],
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
            'status' => $this->service->resolveStatusForDates(
                \Illuminate\Support\Carbon::parse($data['start_date']),
                \Illuminate\Support\Carbon::parse($data['end_date']),
            ),
        ]);
        $delegation->save();

        $this->auditService->record(
            action: 'delegation.updated',
            subject: $delegation,
            before: $before,
            after: $this->auditSnapshot($delegation->fresh()),
            module: 'governance',
            entity: 'approval_delegation',
        );

        return $delegation->fresh();
    }

    public function cancel(ApprovalDelegation $delegation, User $actor): ApprovalDelegation
    {
        if ($delegation->status === DelegationStatus::Cancelled) {
            return $delegation;
        }

        $delegation->update([
            'status' => DelegationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $actor->id,
        ]);

        $this->auditService->record(
            action: 'delegation.cancelled',
            subject: $delegation,
            module: 'governance',
            entity: 'approval_delegation',
        );

        return $delegation->fresh();
    }

    public function usersForCompany(int $companyId): Collection
    {
        return User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * @param  list<string>  $modules
     */
    protected function formatModules(array $modules): string
    {
        if ($modules === []) {
            return __('All modules');
        }

        $labels = collect($modules)
            ->map(fn (string $key) => config("delegation_registry.modules.{$key}.label", $key))
            ->all();

        return implode(', ', $labels);
    }

    /**
     * @param  list<string>  $types
     */
    protected function formatApprovalTypes(array $types): string
    {
        if ($types === []) {
            return __('All approval types');
        }

        $labels = collect($types)
            ->map(fn (string $key) => config("approval_registry.rule_types.{$key}.label", $key))
            ->all();

        return implode(', ', $labels);
    }

    /**
     * @return array<string, mixed>
     */
    protected function auditSnapshot(ApprovalDelegation $delegation): array
    {
        return [
            'delegator_user_id' => $delegation->delegator_user_id,
            'delegate_user_id' => $delegation->delegate_user_id,
            'modules' => $delegation->modules,
            'approval_types' => $delegation->approval_types,
            'reason' => $delegation->reason->value,
            'start_date' => $delegation->start_date->toDateString(),
            'end_date' => $delegation->end_date->toDateString(),
            'status' => $delegation->status->value,
        ];
    }

    protected function assertDistinctUsers(int $delegatorId, int $delegateId): void
    {
        if ($delegatorId === $delegateId) {
            throw new InvalidArgumentException(__('Delegator and delegate must be different users.'));
        }
    }

    protected function assertNoOverlap(
        int $companyId,
        ?int $branchId,
        array $data,
        ?int $ignoreId = null,
    ): void {
        $overlap = ApprovalDelegation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where(function ($scoped) use ($branchId) {
                $scoped->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->where('delegator_user_id', $data['delegator_user_id'])
            ->where('delegate_user_id', $data['delegate_user_id'])
            ->whereIn('status', [DelegationStatus::Scheduled, DelegationStatus::Active])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($data) {
                $query->whereDate('start_date', '<=', $data['end_date'])
                    ->whereDate('end_date', '>=', $data['start_date']);
            })
            ->exists();

        if ($overlap) {
            throw new InvalidArgumentException(__('An overlapping delegation already exists for this delegator and delegate.'));
        }
    }
}
