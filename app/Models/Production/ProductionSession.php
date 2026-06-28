<?php

namespace App\Models\Production;

use App\Enums\ProductionSessionWasteReason;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionSession extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'operator_user_id',
        'started_at', 'ended_at',
        'expected_quantity', 'produced_quantity', 'waste_quantity',
        'waste_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'expected_quantity' => 'decimal:3',
            'produced_quantity' => 'decimal:3',
            'waste_quantity' => 'decimal:3',
            'waste_reason' => ProductionSessionWasteReason::class,
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionSessionMaterial::class);
    }
}
