<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintProfitabilitySnapshot extends Model
{
    use BelongsToTenant;

    protected $table = 'print_profitability_snapshots';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'quotation_id',
        'production_job_card_id',
        'customer_id',
        'machine_profile_id',
        'snapshot_type',
        'revenue',
        'material_cost',
        'ink_cost',
        'machine_cost',
        'labour_cost',
        'electricity_cost',
        'overhead_cost',
        'total_cost',
        'gross_profit',
        'gross_margin_percent',
        'estimated_profit',
        'estimated_margin_percent',
        'profit_variance',
        'margin_variance_percent',
        'profitability_score',
        'profitability_class',
        'snapshot_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_type' => ProfitabilitySnapshotType::class,
            'profitability_class' => ProfitabilityClass::class,
            'revenue' => 'decimal:2',
            'material_cost' => 'decimal:2',
            'ink_cost' => 'decimal:2',
            'machine_cost' => 'decimal:2',
            'labour_cost' => 'decimal:2',
            'electricity_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'gross_margin_percent' => 'decimal:3',
            'estimated_profit' => 'decimal:2',
            'estimated_margin_percent' => 'decimal:3',
            'profit_variance' => 'decimal:2',
            'margin_variance_percent' => 'decimal:3',
            'profitability_score' => 'decimal:2',
            'snapshot_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
