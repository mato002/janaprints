<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\InkEstimationStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintArtworkInkEstimate extends Model
{
    use BelongsToTenant;

    protected $table = 'print_artwork_ink_estimates';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'print_artwork_analysis_id',
        'ink_profile_id',
        'estimation_status',
        'coverage_percent',
        'coverage_area_sq_m',
        'estimated_cyan_ml',
        'estimated_magenta_ml',
        'estimated_yellow_ml',
        'estimated_black_ml',
        'estimated_total_ml',
        'estimated_cartridge_percent',
        'estimated_ink_cost',
        'confidence_score',
        'formula_version',
        'metadata',
        'warnings',
        'estimated_at',
    ];

    protected function casts(): array
    {
        return [
            'estimation_status' => InkEstimationStatus::class,
            'coverage_percent' => 'decimal:3',
            'coverage_area_sq_m' => 'decimal:6',
            'estimated_cyan_ml' => 'decimal:4',
            'estimated_magenta_ml' => 'decimal:4',
            'estimated_yellow_ml' => 'decimal:4',
            'estimated_black_ml' => 'decimal:4',
            'estimated_total_ml' => 'decimal:4',
            'estimated_cartridge_percent' => 'decimal:3',
            'estimated_ink_cost' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'metadata' => 'array',
            'warnings' => 'array',
            'estimated_at' => 'datetime',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkAnalysis::class, 'print_artwork_analysis_id');
    }

    public function inkProfile(): BelongsTo
    {
        return $this->belongsTo(PrintInkProfile::class, 'ink_profile_id');
    }
}
