<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Exceptions\PrintingIntelligence\AppliedEstimateImmutableException;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Inventory\InventoryItem;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintQuotationEstimate extends Model
{
    use BelongsToTenant;

    protected $table = 'print_quotation_estimates';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'quotation_id',
        'print_artwork_analysis_id',
        'print_artwork_ink_estimate_id',
        'print_machine_estimate_id',
        'estimation_status',
        'quantity',
        'version',
        'material_inventory_item_id',
        'material_name',
        'material_unit_cost',
        'material_quantity',
        'estimated_material_cost',
        'estimated_ink_cost',
        'estimated_machine_cost',
        'estimated_labour_cost',
        'estimated_electricity_cost',
        'estimated_overhead_cost',
        'estimated_wastage_cost',
        'estimated_total_cost',
        'minimum_margin_percent',
        'target_margin_percent',
        'minimum_selling_price',
        'recommended_selling_price',
        'expected_margin_percent',
        'confidence_score',
        'formula_version',
        'calculation_breakdown',
        'warnings',
        'metadata',
        'applied_at',
        'applied_by',
    ];

    protected function casts(): array
    {
        return [
            'estimation_status' => QuotationEstimationStatus::class,
            'quantity' => 'integer',
            'version' => 'integer',
            'material_unit_cost' => 'decimal:4',
            'material_quantity' => 'decimal:6',
            'estimated_material_cost' => 'decimal:2',
            'estimated_ink_cost' => 'decimal:2',
            'estimated_machine_cost' => 'decimal:2',
            'estimated_labour_cost' => 'decimal:2',
            'estimated_electricity_cost' => 'decimal:2',
            'estimated_overhead_cost' => 'decimal:2',
            'estimated_wastage_cost' => 'decimal:2',
            'estimated_total_cost' => 'decimal:2',
            'minimum_margin_percent' => 'decimal:3',
            'target_margin_percent' => 'decimal:3',
            'minimum_selling_price' => 'decimal:2',
            'recommended_selling_price' => 'decimal:2',
            'expected_margin_percent' => 'decimal:3',
            'confidence_score' => 'decimal:2',
            'calculation_breakdown' => 'array',
            'warnings' => 'array',
            'metadata' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkAnalysis::class, 'print_artwork_analysis_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function inkEstimate(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkInkEstimate::class, 'print_artwork_ink_estimate_id');
    }

    public function productionEstimate(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkProductionEstimate::class, 'print_machine_estimate_id');
    }

    public function materialItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'material_inventory_item_id');
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function actualComparisons(): HasMany
    {
        return $this->hasMany(PrintEstimateActualComparison::class, 'print_quotation_estimate_id');
    }

    protected static function booted(): void
    {
        static::updating(function (PrintQuotationEstimate $estimate): void {
            if ($estimate->getOriginal('applied_at') === null) {
                return;
            }

            $dirty = collect($estimate->getDirty())->except(['updated_at'])->all();

            if ($dirty !== []) {
                throw AppliedEstimateImmutableException::forEstimate((int) $estimate->id);
            }
        });
    }
}
