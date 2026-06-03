<?php

namespace App\Models\Platform;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'rule_type',
        'is_enabled',
        'threshold_amount',
        'threshold_percent',
        'approver_role',
        'min_approvers',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'threshold_amount' => 'decimal:2',
            'threshold_percent' => 'decimal:2',
            'min_approvers' => 'integer',
            'settings_json' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
