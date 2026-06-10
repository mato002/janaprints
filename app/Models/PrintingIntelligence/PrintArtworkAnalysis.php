<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\ArtworkAnalysisSource;
use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\CoverageClass;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Artwork\ArtworkFile;
use App\Models\Production\ProductionJobCard;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintArtworkAnalysis extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'quotation_id',
        'production_job_card_id',
        'public_quote_request_id',
        'artwork_file_id',
        'source_file_model',
        'source_file_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'file_path',
        'disk',
        'mime_type',
        'file_extension',
        'file_size_bytes',
        'file_hash',
        'analysis_status',
        'analysis_source',
        'page_count',
        'width_mm',
        'height_mm',
        'area_square_m',
        'resolution_dpi',
        'colour_mode',
        'has_transparency',
        'has_bleed',
        'has_crop_marks',
        'dominant_colours',
        'colour_analysis_status',
        'rgb_coverage_percent',
        'cmyk_coverage_percent',
        'cyan_coverage_percent',
        'magenta_coverage_percent',
        'yellow_coverage_percent',
        'black_coverage_percent',
        'white_area_percent',
        'transparent_area_percent',
        'average_ink_density_percent',
        'heavy_coverage_score',
        'coverage_class',
        'colour_analysis_warnings',
        'colour_analysis_raw',
        'colour_analyzed_at',
        'metadata',
        'warnings',
        'errors',
        'analyzed_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'analysis_status' => ArtworkAnalysisStatus::class,
            'analysis_source' => ArtworkAnalysisSource::class,
            'file_size_bytes' => 'integer',
            'page_count' => 'integer',
            'width_mm' => 'decimal:3',
            'height_mm' => 'decimal:3',
            'area_square_m' => 'decimal:6',
            'resolution_dpi' => 'decimal:2',
            'has_transparency' => 'boolean',
            'has_bleed' => 'boolean',
            'has_crop_marks' => 'boolean',
            'dominant_colours' => 'array',
            'colour_analysis_status' => ColourAnalysisStatus::class,
            'coverage_class' => CoverageClass::class,
            'rgb_coverage_percent' => 'decimal:3',
            'cmyk_coverage_percent' => 'decimal:3',
            'cyan_coverage_percent' => 'decimal:3',
            'magenta_coverage_percent' => 'decimal:3',
            'yellow_coverage_percent' => 'decimal:3',
            'black_coverage_percent' => 'decimal:3',
            'white_area_percent' => 'decimal:3',
            'transparent_area_percent' => 'decimal:3',
            'average_ink_density_percent' => 'decimal:3',
            'heavy_coverage_score' => 'decimal:3',
            'colour_analysis_warnings' => 'array',
            'colour_analysis_raw' => 'array',
            'colour_analyzed_at' => 'datetime',
            'metadata' => 'array',
            'warnings' => 'array',
            'errors' => 'array',
            'analyzed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function productionJobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class);
    }

    public function publicQuoteRequest(): BelongsTo
    {
        return $this->belongsTo(PublicQuoteRequest::class);
    }

    public function artworkFile(): BelongsTo
    {
        return $this->belongsTo(ArtworkFile::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PrintArtworkPage::class)->orderBy('page_number');
    }

    public function inkEstimates(): HasMany
    {
        return $this->hasMany(PrintArtworkInkEstimate::class)->latest('id');
    }

    public function productionEstimate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PrintArtworkProductionEstimate::class, 'print_artwork_analysis_id');
    }

    public function quotationEstimates(): HasMany
    {
        return $this->hasMany(PrintQuotationEstimate::class)->latest('id');
    }
}
