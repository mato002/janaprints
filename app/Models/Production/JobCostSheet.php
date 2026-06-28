<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCostSheet extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'production_job_card_id',
        'status',
        'material_cost',
        'wastage_cost',
        'labor_cost',
        'machine_cost',
        'finishing_cost',
        'outsourced_cost',
        'overhead_cost',
        'total_cost',
        'revenue',
        'gross_profit',
        'gross_margin_percent',
        'net_profit',
        'net_margin_percent',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'material_cost' => 'decimal:2',
            'wastage_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'machine_cost' => 'decimal:2',
            'finishing_cost' => 'decimal:2',
            'outsourced_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'revenue' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'gross_margin_percent' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'net_margin_percent' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JobCostLine::class);
    }
}
