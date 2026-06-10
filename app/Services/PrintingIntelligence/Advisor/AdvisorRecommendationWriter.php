<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationStatus;
use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;

class AdvisorRecommendationWriter
{
    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     * @return list<PrintAdvisorRecommendation>
     */
    public function persist(int $companyId, ?int $branchId, array $recommendations, bool $persist = true): array
    {
        $saved = [];

        foreach ($recommendations as $rec) {
            if (! $persist) {
                $saved[] = new PrintAdvisorRecommendation(array_merge($rec, [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'status' => AdvisorRecommendationStatus::Open,
                    'generated_at' => now(),
                ]));

                continue;
            }

            $existing = PrintAdvisorRecommendation::query()
                ->where('company_id', $companyId)
                ->where('rule_code', $rec['rule_code'])
                ->first();

            if ($existing !== null && $existing->status !== AdvisorRecommendationStatus::Open) {
                continue;
            }

            $saved[] = PrintAdvisorRecommendation::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'rule_code' => $rec['rule_code'],
                ],
                array_merge($rec, [
                    'branch_id' => $branchId,
                    'status' => $existing?->status ?? AdvisorRecommendationStatus::Open,
                    'generated_at' => now(),
                    'acknowledged_by' => $existing?->acknowledged_by,
                    'acknowledged_at' => $existing?->acknowledged_at,
                    'dismissed_by' => $existing?->dismissed_by,
                    'dismissed_at' => $existing?->dismissed_at,
                    'comment' => $existing?->comment,
                ]),
            );
        }

        return $saved;
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(
        AdvisorRecommendationType $type,
        AdvisorSeverity $severity,
        string $ruleCode,
        string $title,
        string $summary,
        string $text,
        string $sourceModule,
        float $confidence,
        ?string $action = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $evidence = [],
    ): array {
        return [
            'recommendation_type' => $type,
            'severity' => $severity,
            'title' => $title,
            'summary' => $summary,
            'recommendation_text' => $text,
            'source_module' => $sourceModule,
            'confidence_score' => $confidence,
            'recommended_action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'rule_code' => $ruleCode,
            'evidence' => array_merge($evidence, ['rule_code' => $ruleCode]),
        ];
    }
}
