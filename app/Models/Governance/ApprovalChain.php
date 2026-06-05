<?php

namespace App\Models\Governance;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalRuleType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalChain extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'module',
        'document_type',
        'approval_rule_type',
        'approval_mode',
        'status',
        'description',
        'condition_json',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'approval_mode' => ApprovalChainMode::class,
            'status' => ApprovalChainStatus::class,
            'approval_rule_type' => ApprovalRuleType::class,
            'condition_json' => 'array',
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

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalChainStep::class)->orderBy('step_number');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ApprovalChainRun::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
