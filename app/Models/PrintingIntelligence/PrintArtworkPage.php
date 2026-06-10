<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\CoverageClass;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintArtworkPage extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'print_artwork_analysis_id',
        'page_number',
        'width_mm',
        'height_mm',
        'area_square_m',
        'resolution_dpi',
        'colour_mode',
        'rgb_coverage_percent',
        'cmyk_coverage_percent',
        'cyan_coverage_percent',
        'magenta_coverage_percent',
        'yellow_coverage_percent',
        'black_coverage_percent',
        'white_area_percent',
        'transparent_area_percent',
        'dominant_colours',
        'coverage_class',
        'colour_analysis_raw',
        'metadata',
        'warnings',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'width_mm' => 'decimal:3',
            'height_mm' => 'decimal:3',
            'area_square_m' => 'decimal:6',
            'resolution_dpi' => 'decimal:2',
            'rgb_coverage_percent' => 'decimal:3',
            'cmyk_coverage_percent' => 'decimal:3',
            'cyan_coverage_percent' => 'decimal:3',
            'magenta_coverage_percent' => 'decimal:3',
            'yellow_coverage_percent' => 'decimal:3',
            'black_coverage_percent' => 'decimal:3',
            'white_area_percent' => 'decimal:3',
            'transparent_area_percent' => 'decimal:3',
            'dominant_colours' => 'array',
            'coverage_class' => CoverageClass::class,
            'colour_analysis_raw' => 'array',
            'metadata' => 'array',
            'warnings' => 'array',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(PrintArtworkAnalysis::class, 'print_artwork_analysis_id');
    }
}
