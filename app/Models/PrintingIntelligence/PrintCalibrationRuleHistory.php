<?php

namespace App\Models\PrintingIntelligence;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintCalibrationRuleHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'print_calibration_rule_history';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'print_calibration_rule_id',
        'company_id',
        'before_value',
        'after_value',
        'rule_version',
        'approved_by',
        'approved_at',
        'effective_from',
        'reason',
        'metadata',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'before_value' => 'decimal:6',
            'after_value' => 'decimal:6',
            'approved_at' => 'datetime',
            'effective_from' => 'datetime',
            'recorded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PrintCalibrationRule::class, 'print_calibration_rule_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
