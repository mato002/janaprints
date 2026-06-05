<?php

namespace App\Models\Assets;

use App\Enums\AssetReconciliationStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRegisterReconciliation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'reconciliation_no',
        'reconciliation_date',
        'register_cost',
        'register_accumulated',
        'register_nbv',
        'gl_cost',
        'gl_accumulated',
        'gl_nbv',
        'variance_cost',
        'variance_nbv',
        'status',
        'findings',
        'reconciled_by',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'register_cost' => 'decimal:2',
            'register_accumulated' => 'decimal:2',
            'register_nbv' => 'decimal:2',
            'gl_cost' => 'decimal:2',
            'gl_accumulated' => 'decimal:2',
            'gl_nbv' => 'decimal:2',
            'variance_cost' => 'decimal:2',
            'variance_nbv' => 'decimal:2',
            'status' => AssetReconciliationStatus::class,
            'findings' => 'array',
        ];
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
