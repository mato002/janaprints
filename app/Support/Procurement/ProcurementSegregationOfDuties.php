<?php

namespace App\Support\Procurement;

use App\Enums\ApprovalRuleType;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use App\Support\Platform\SystemSettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProcurementSegregationOfDuties
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function assertCanApprove(
        Model $subject,
        User $actor,
        ApprovalRuleType $ruleType,
        ?int $submitterUserId = null,
    ): void {
        $companyId = (int) $subject->getAttribute('company_id');
        $branchId = $subject->getAttribute('branch_id');

        if ($this->settingEnabled($companyId, $branchId, 'procurement_enforce_requester_approver_separation')) {
            $requesterId = $submitterUserId ?? (int) ($subject->getAttribute('requested_by') ?? $subject->getAttribute('prepared_by') ?? 0);

            if ($requesterId > 0 && $requesterId === $actor->id) {
                throw ValidationException::withMessages([
                    'approval' => __('Requester cannot approve their own procurement document.'),
                ]);
            }
        }

        if ($ruleType === ApprovalRuleType::ProcurementApproval
            && $this->settingEnabled($companyId, $branchId, 'procurement_enforce_pr_po_approver_separation')) {
            $prApproverId = 0;

            if ($subject instanceof PurchaseOrder) {
                $subject->loadMissing('purchaseRequest');
                $prApproverId = (int) ($subject->purchaseRequest?->approved_by ?? 0);
            }

            if ($prApproverId > 0 && $prApproverId === $actor->id) {
                throw ValidationException::withMessages([
                    'approval' => __('Purchase request approver cannot approve the related purchase order.'),
                ]);
            }
        }
    }

    protected function settingEnabled(int $companyId, ?int $branchId, string $key): bool
    {
        return (bool) $this->settings->get($key, false, $companyId, $branchId);
    }
}
