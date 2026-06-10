<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\Quotation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintEstimateActualComparison extends Model
{
    use BelongsToTenant;

    protected $table = 'print_estimate_actual_comparisons';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'print_quotation_estimate_id',
        'quotation_id',
        'production_job_card_id',
        'job_cost_sheet_id',
        'production_output_id',
        'comparison_status',
        'estimated_material_cost',
        'actual_material_cost',
        'material_cost_variance',
        'material_cost_variance_percent',
        'estimated_ink_cost',
        'actual_ink_cost',
        'ink_cost_variance',
        'ink_cost_variance_percent',
        'estimated_machine_cost',
        'actual_machine_cost',
        'machine_cost_variance',
        'machine_cost_variance_percent',
        'estimated_labour_cost',
        'actual_labour_cost',
        'labour_cost_variance',
        'labour_cost_variance_percent',
        'estimated_overhead_cost',
        'actual_overhead_cost',
        'overhead_cost_variance',
        'overhead_cost_variance_percent',
        'estimated_total_cost',
        'actual_total_cost',
        'total_cost_variance',
        'total_cost_variance_percent',
        'recommended_price',
        'actual_selling_price',
        'estimated_margin_percent',
        'actual_margin_percent',
        'margin_variance_percent',
        'accuracy_score',
        'confidence_score',
        'variance_class',
        'recommendation',
        'calculation_breakdown',
        'warnings',
        'metadata',
        'compared_at',
    ];

    protected function casts(): array
    {
        return [
            'comparison_status' => EstimateActualComparisonStatus::class,
            'variance_class' => EstimateVarianceClass::class,
            'estimated_material_cost' => 'decimal:2',
            'actual_material_cost' => 'decimal:2',
            'material_cost_variance' => 'decimal:2',
            'material_cost_variance_percent' => 'decimal:3',
            'estimated_ink_cost' => 'decimal:2',
            'actual_ink_cost' => 'decimal:2',
            'ink_cost_variance' => 'decimal:2',
            'ink_cost_variance_percent' => 'decimal:3',
            'estimated_machine_cost' => 'decimal:2',
            'actual_machine_cost' => 'decimal:2',
            'machine_cost_variance' => 'decimal:2',
            'machine_cost_variance_percent' => 'decimal:3',
            'estimated_labour_cost' => 'decimal:2',
            'actual_labour_cost' => 'decimal:2',
            'labour_cost_variance' => 'decimal:2',
            'labour_cost_variance_percent' => 'decimal:3',
            'estimated_overhead_cost' => 'decimal:2',
            'actual_overhead_cost' => 'decimal:2',
            'overhead_cost_variance' => 'decimal:2',
            'overhead_cost_variance_percent' => 'decimal:3',
            'estimated_total_cost' => 'decimal:2',
            'actual_total_cost' => 'decimal:2',
            'total_cost_variance' => 'decimal:2',
            'total_cost_variance_percent' => 'decimal:3',
            'recommended_price' => 'decimal:2',
            'actual_selling_price' => 'decimal:2',
            'estimated_margin_percent' => 'decimal:3',
            'actual_margin_percent' => 'decimal:3',
            'margin_variance_percent' => 'decimal:3',
            'accuracy_score' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'calculation_breakdown' => 'array',
            'warnings' => 'array',
            'metadata' => 'array',
            'compared_at' => 'datetime',
        ];
    }

    public function quotationEstimate(): BelongsTo
    {
        return $this->belongsTo(PrintQuotationEstimate::class, 'print_quotation_estimate_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function jobCostSheet(): BelongsTo
    {
        return $this->belongsTo(JobCostSheet::class);
    }

    public function productionOutput(): BelongsTo
    {
        return $this->belongsTo(ProductionOutput::class);
    }
}
