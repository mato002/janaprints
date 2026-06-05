<?php

namespace App\Models\Governance;

use App\Enums\ApprovalChainRunStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalChainRun extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'approval_chain_id',
        'approval_rule_type',
        'subject_type',
        'subject_id',
        'status',
        'context_json',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalChainRunStatus::class,
            'approval_rule_type' => ApprovalRuleType::class,
            'context_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function chain(): BelongsTo
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }

    public function stepRecords(): HasMany
    {
        return $this->hasMany(ApprovalChainStepRecord::class)->orderBy('step_number');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
