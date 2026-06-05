<?php

namespace App\Policies\Concerns;

use App\Enums\ApprovalRuleType;
use App\Enums\DocumentModule;
use App\Models\User;
use App\Support\Platform\ApprovalDelegationService;

trait ChecksApprovalDelegation
{
    protected function canApproveViaDelegation(
        User $user,
        ApprovalRuleType $approvalType,
        DocumentModule $module,
        int $companyId,
        ?int $branchId,
        string $requiredPermission,
    ): bool {
        return app(ApprovalDelegationService::class)->canActAsDelegate(
            $user,
            $approvalType->value,
            $module->value,
            $companyId,
            $branchId,
            $requiredPermission,
        );
    }
}
