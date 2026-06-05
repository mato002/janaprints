<?php

namespace App\Models\Pos;

use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'cashier_id', 'session_number', 'terminal',
        'opening_float', 'opening_cash', 'expected_cash', 'expected_mpesa', 'expected_card',
        'expected_bank', 'expected_total', 'actual_cash', 'variance', 'variance_requires_approval',
        'status', 'opened_at', 'closed_at', 'opened_by', 'closed_by',
        'variance_approved_by', 'variance_approved_at',
        'opening_notes', 'closing_notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_float' => 'decimal:2',
            'opening_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'expected_mpesa' => 'decimal:2',
            'expected_card' => 'decimal:2',
            'expected_bank' => 'decimal:2',
            'expected_total' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'variance' => 'decimal:2',
            'variance_requires_approval' => 'boolean',
            'status' => PosSessionStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'variance_approved_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function varianceApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'variance_approved_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    public function cashReconciliation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PosCashReconciliation::class, 'pos_session_id');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $session = static::query()->forTenant()->where($field, $value)->first();

        if ($session === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $session;
    }
}
