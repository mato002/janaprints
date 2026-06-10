<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrintCalibrationRule extends Model
{
    use BelongsToTenant;

    protected $table = 'print_calibration_rules';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'rule_type',
        'rule_key',
        'current_value',
        'proposed_value',
        'variance_trigger_percent',
        'status',
        'reason',
        'created_by',
        'reviewed_by',
        'approved_by',
        'approved_at',
        'effective_from',
        'rule_version',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rule_type' => CalibrationRuleType::class,
            'status' => CalibrationRuleStatus::class,
            'current_value' => 'decimal:6',
            'proposed_value' => 'decimal:6',
            'variance_trigger_percent' => 'decimal:3',
            'approved_at' => 'datetime',
            'effective_from' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(PrintCalibrationRuleHistory::class, 'print_calibration_rule_id');
    }

    public function approvalRuns(): MorphMany
    {
        return $this->morphMany(\App\Models\Governance\ApprovalChainRun::class, 'subject');
    }
}
