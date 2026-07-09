<?php

namespace App\Models\Production;

use App\Enums\QualityCheckResult;
use App\Enums\QualityFailReason;
use App\Enums\QualityReworkReason;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheck extends Model
{
    use BelongsToTenant, HasPublicHash;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'checked_by',
        'result', 'comments', 'checked_at', 'inspection_date',
        'checklist_results', 'fail_reason', 'rework_reason',
        'estimated_rework_qty', 'actual_rework_qty',
        'requires_customer_approval', 'customer_approved_by', 'customer_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => QualityCheckResult::class,
            'checked_at' => 'datetime',
            'inspection_date' => 'date',
            'checklist_results' => 'array',
            'fail_reason' => QualityFailReason::class,
            'rework_reason' => QualityReworkReason::class,
            'estimated_rework_qty' => 'decimal:3',
            'actual_rework_qty' => 'decimal:3',
            'requires_customer_approval' => 'boolean',
            'customer_approved_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function customerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_approved_by');
    }
}
