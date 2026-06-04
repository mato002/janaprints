<?php

namespace App\Support\Tax;

use App\Models\Tax\TaxAuditLog;
use Illuminate\Database\Eloquent\Model;

class TaxAuditService
{
    public function log(
        int $companyId,
        ?int $userId,
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        TaxAuditLog::query()->create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'auditable_type' => $auditable ? $auditable::class : 'system',
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
