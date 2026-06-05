<?php

namespace App\Models\Pos;

use App\Enums\PosReconciliationStatus;
use App\Enums\PosVarianceType;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosCashReconciliation extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'pos_session_id', 'cashier_id', 'reconciliation_number',
        'opening_float', 'cash_sales', 'mpesa_sales', 'card_sales',
        'refunds_count', 'refund_total', 'expected_cash', 'actual_cash', 'variance',
        'variance_type', 'status', 'notes',
        'submitted_by', 'submitted_at', 'reviewed_by', 'reviewed_at', 'review_notes',
        'approved_by', 'approved_at', 'approval_notes', 'posted_journal_id',
        'rejected_by', 'rejected_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'opening_float' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'mpesa_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'refund_total' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'variance' => 'decimal:2',
            'variance_type' => PosVarianceType::class,
            'status' => PosReconciliationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PosCashReconciliationLog::class)->orderByDesc('created_at');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $reconciliation = static::query()->forTenant()->where($field, $value)->first();

        if ($reconciliation === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $reconciliation;
    }
}
