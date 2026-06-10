<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\ProductionEstimationStatus;
use App\Models\Assets\MachineProfile;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintArtworkProductionEstimate extends Model
{
    use BelongsToTenant;

    protected $table = 'print_artwork_production_estimates';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'print_artwork_analysis_id',
        'machine_profile_id',
        'estimation_status',
        'quantity',
        'total_area_sq_m',
        'estimated_run_hours',
        'estimated_setup_cost',
        'estimated_electricity_cost',
        'estimated_machine_cost',
        'estimated_labour_cost',
        'estimated_ink_cost',
        'estimated_material_cost',
        'estimated_overhead_cost',
        'estimated_total_production_cost',
        'selection_score',
        'confidence_score',
        'formula_version',
        'machine_alternatives',
        'metadata',
        'warnings',
        'estimated_at',
    ];

    protected function casts(): array
    {
        return [
            'estimation_status' => ProductionEstimationStatus::class,
            'quantity' => 'integer',
            'total_area_sq_m' => 'decimal:6',
            'estimated_run_hours' => 'decimal:4',
            'estimated_setup_cost' => 'decimal:2',
            'estimated_electricity_cost' => 'decimal:2',
            'estimated_machine_cost' => 'decimal:2',
            'estimated_labour_cost' => 'decimal:2',
            'estimated_ink_cost' => 'decimal:2',
            'estimated_material_cost' => 'decimal:2',
            'estimated_overhead_cost' => 'decimal:2',
            'estimated_total_production_cost' => 'decimal:2',
            'selection_score' => 'decimal:3',
            'confidence_score' => 'decimal:2',
            'machine_alternatives' => 'array',
            'metadata' => 'array',
            'warnings' => 'array',
            'estimated_at' => 'datetime',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkAnalysis::class, 'print_artwork_analysis_id');
    }

    public function machineProfile(): BelongsTo
    {
        return $this->belongsTo(MachineProfile::class, 'machine_profile_id');
    }
}
