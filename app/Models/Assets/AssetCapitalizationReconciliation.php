<?php

namespace App\Models\Assets;

use App\Enums\AssetCapitalizationReconciliationStatus;
use App\Models\Company;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetCapitalizationReconciliation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'reconciliation_number',
        'reconciliation_date',
        'procurement_received_value',
        'capitalized_value',
        'posted_value',
        'register_value',
        'received_not_capitalized_count',
        'capitalized_not_posted_count',
        'posted_not_registered_count',
        'status',
        'variance_details',
        'run_by',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'procurement_received_value' => 'decimal:2',
            'capitalized_value' => 'decimal:2',
            'posted_value' => 'decimal:2',
            'register_value' => 'decimal:2',
            'status' => AssetCapitalizationReconciliationStatus::class,
            'variance_details' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }
}
