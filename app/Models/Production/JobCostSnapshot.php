<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCostSnapshot extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'job_cost_sheet_id',
        'snapshot_reason', 'revenue', 'material_cost', 'labor_cost', 'machine_cost',
        'outsourced_cost', 'overhead_cost', 'total_cost', 'gross_profit',
        'gross_margin_percent', 'snapshot_at',
    ];

    protected function casts(): array
    {
        return [
            'revenue' => 'decimal:2',
            'material_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'machine_cost' => 'decimal:2',
            'outsourced_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'gross_margin_percent' => 'decimal:2',
            'snapshot_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(JobCostSheet::class, 'job_cost_sheet_id');
    }
}
