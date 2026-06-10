<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorRecommendationType;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;
use App\Models\User;

class AdvisorRecommendationWorkflowService
{
    public function acknowledge(PrintAdvisorRecommendation $recommendation, User $user, ?string $comment = null): PrintAdvisorRecommendation
    {
        $recommendation->update([
            'status' => AdvisorRecommendationStatus::Acknowledged,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'comment' => $comment ?? $recommendation->comment,
        ]);

        return $recommendation->fresh();
    }

    public function dismiss(PrintAdvisorRecommendation $recommendation, User $user, ?string $comment = null): PrintAdvisorRecommendation
    {
        $recommendation->update([
            'status' => AdvisorRecommendationStatus::Dismissed,
            'dismissed_by' => $user->id,
            'dismissed_at' => now(),
            'comment' => $comment ?? $recommendation->comment,
        ]);

        return $recommendation->fresh();
    }
}
