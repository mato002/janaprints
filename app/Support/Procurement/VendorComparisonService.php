<?php

namespace App\Support\Procurement;

use App\Enums\RfqStatus;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\VendorComparison;
use Illuminate\Support\Facades\DB;

class VendorComparisonService
{
    /**
     * @return array{matrix: array<int, array<string, mixed>>, highlights: array<string, int|null>, recommended_vendor_id: int|null}
     */
    public static function buildMatrix(Rfq $rfq): array
    {
        $rfq->load(['items', 'vendors.vendor', 'responses.rfqItem']);

        $vendors = [];
        $lowestPriceId = null;
        $lowestTotal = null;
        $bestLeadId = null;
        $bestLead = null;
        $bestScoreId = null;
        $bestScore = null;

        foreach ($rfq->vendors as $rfqVendor) {
            $responses = $rfq->responses->where('rfq_vendor_id', $rfqVendor->id);
            $total = 0.0;
            $leadSum = 0;
            $leadCount = 0;

            $lineScores = [];

            foreach ($rfq->items as $item) {
                $response = $responses->firstWhere('rfq_item_id', $item->id);
                $price = (float) ($response?->quoted_price ?? 0);
                $total += $price * (float) $item->quantity;

                if ($response?->lead_time_days) {
                    $leadSum += (int) $response->lead_time_days;
                    $leadCount++;
                }
            }

            $avgLead = $leadCount > 0 ? (int) round($leadSum / $leadCount) : null;
            $priceScore = $lowestTotal === null || $total < $lowestTotal ? 100 : max(0, 100 - (int) (($total - ($lowestTotal ?? $total)) / max($lowestTotal ?? 1, 1) * 100));
            $leadScore = $bestLead === null || ($avgLead !== null && $avgLead <= $bestLead) ? 100 : max(0, 100 - (($avgLead ?? 0) - ($bestLead ?? 0)) * 5);
            $score = (int) round(($priceScore * 0.6) + ($leadScore * 0.4));

            $vendors[$rfqVendor->vendor_id] = [
                'vendor_id' => $rfqVendor->vendor_id,
                'vendor_name' => $rfqVendor->vendor->vendor_name,
                'total_price' => round($total, 2),
                'avg_lead_time_days' => $avgLead,
                'score' => $score,
                'lines' => $rfq->items->map(function ($item) use ($responses) {
                    $response = $responses->firstWhere('rfq_item_id', $item->id);

                    return [
                        'rfq_item_id' => $item->id,
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'quoted_price' => (float) ($response?->quoted_price ?? 0),
                        'lead_time_days' => $response?->lead_time_days,
                    ];
                })->values()->all(),
            ];

            if ($lowestTotal === null || $total < $lowestTotal) {
                $lowestTotal = $total;
                $lowestPriceId = $rfqVendor->vendor_id;
            }

            if ($avgLead !== null && ($bestLead === null || $avgLead < $bestLead)) {
                $bestLead = $avgLead;
                $bestLeadId = $rfqVendor->vendor_id;
            }

            if ($bestScore === null || $score > $bestScore) {
                $bestScore = $score;
                $bestScoreId = $rfqVendor->vendor_id;
            }
        }

        return [
            'matrix' => array_values($vendors),
            'highlights' => [
                'lowest_price_vendor_id' => $lowestPriceId,
                'best_lead_time_vendor_id' => $bestLeadId,
                'best_score_vendor_id' => $bestScoreId,
            ],
            'recommended_vendor_id' => $bestScoreId,
        ];
    }

    public static function persistComparison(Rfq $rfq, int $userId, ?string $notes = null): VendorComparison
    {
        $built = self::buildMatrix($rfq);

        return DB::transaction(function () use ($rfq, $userId, $notes, $built) {
            $comparison = VendorComparison::query()->updateOrCreate(
                ['rfq_id' => $rfq->id],
                [
                    'company_id' => $rfq->company_id,
                    'branch_id' => $rfq->branch_id,
                    'comparison_date' => now()->toDateString(),
                    'status' => 'finalized',
                    'recommended_vendor_id' => $built['recommended_vendor_id'],
                    'recommendation_notes' => $notes,
                    'matrix' => $built,
                    'created_by' => $userId,
                ],
            );

            if ($rfq->status === RfqStatus::AwaitingComparison) {
                $rfq->update(['status' => RfqStatus::Closed]);
            }

            return $comparison->fresh(['recommendedVendor']);
        });
    }
}
