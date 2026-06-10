<?php

namespace App\Models\PrintingIntelligence;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintAdvisorRecommendation extends Model
{
    use BelongsToTenant;

    protected $table = 'print_advisor_recommendations';

    protected bool $tenantScopedToBranch = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'recommendation_type',
        'severity',
        'status',
        'title',
        'summary',
        'recommendation_text',
        'source_module',
        'confidence_score',
        'evidence',
        'recommended_action',
        'entity_type',
        'entity_id',
        'rule_code',
        'comment',
        'generated_at',
        'acknowledged_by',
        'acknowledged_at',
        'dismissed_by',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_type' => AdvisorRecommendationType::class,
            'severity' => AdvisorSeverity::class,
            'status' => AdvisorRecommendationStatus::class,
            'confidence_score' => 'decimal:2',
            'evidence' => 'array',
            'generated_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function dismissedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }
}
