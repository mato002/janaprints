<?php

namespace App\Support\Procurement;

use App\Enums\RfqStatus;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\VendorComparison;
use Illuminate\Support\Facades\DB;

class VendorComparisonService
{
    /**
     * @return array<string, int>
     */
    public static function defaultWeights(): array
    {
        return config('procurement_scoring.weights', [
            'price' => 40,
            'performance' => 25,
            'lead_time' => 20,
            'quality' => 15,
        ]);
    }

    /**
     * @param  array<string, int>|null  $weights
     * @return array{
     *     rfq: array<string, mixed>,
     *     items: list<array<string, mixed>>,
     *     matrix: array<int, array<string, mixed>>,
     *     highlights: array<string, int|null>,
     *     recommended_vendor_id: int|null,
     *     weights: array<string, int>
     * }
     */
    public static function buildWorkspace(Rfq $rfq, ?array $weights = null): array
    {
        $weights = self::normalizeWeights($weights ?? self::defaultWeights());
        $rfq->load(['items.inventoryItem', 'vendors.vendor', 'responses.rfqItem', 'purchaseRequest']);

        $performance = app(VendorPerformanceService::class);
        $vendors = [];
        $lowestPriceId = null;
        $lowestTotal = null;
        $bestLeadId = null;
        $bestLead = null;
        $bestScoreId = null;
        $bestScore = null;

        $priceTotals = [];
        $leadTimes = [];
        $performanceScores = [];
        $qualityScores = [];

        foreach ($rfq->vendors as $rfqVendor) {
            if ($rfqVendor->invitation_status === 'rejected') {
                continue;
            }

            $responses = $rfq->responses->where('rfq_vendor_id', $rfqVendor->id);
            $total = 0.0;
            $leadSum = 0;
            $leadCount = 0;
            $warrantyCount = 0;
            $responseCount = 0;
            $avgQuotedPrice = 0.0;
            $priceSum = 0.0;

            foreach ($rfq->items as $item) {
                $response = $responses->firstWhere('rfq_item_id', $item->id);
                $price = (float) ($response?->quoted_price ?? 0);
                $total += $price * (float) $item->quantity;
                $priceSum += $price;
                $responseCount++;

                if ($response?->lead_time_days) {
                    $leadSum += (int) $response->lead_time_days;
                    $leadCount++;
                }

                if (filled($response?->warranty)) {
                    $warrantyCount++;
                }
            }

            $avgLead = $leadCount > 0 ? (int) round($leadSum / $leadCount) : null;
            $avgQuotedPrice = $responseCount > 0 ? round($priceSum / $responseCount, 2) : 0.0;
            $metrics = $performance->metrics($rfqVendor->vendor, $rfq->company_id, $rfq->branch_id);

            $priceTotals[$rfqVendor->vendor_id] = $total;
            $leadTimes[$rfqVendor->vendor_id] = $avgLead;
            $performanceScores[$rfqVendor->vendor_id] = $metrics['performance_percent'];
            $qualityScores[$rfqVendor->vendor_id] = $rfq->items->count() > 0
                ? round(($warrantyCount / $rfq->items->count()) * 100, 1)
                : 0.0;

            $firstResponse = $responses->first();
            $paymentTerms = $rfqVendor->vendor->payment_terms ?? '—';
            $deliveryTerms = $firstResponse?->delivery_terms ?? '—';
            $warranty = $firstResponse?->warranty ?? '—';

            $vendors[$rfqVendor->vendor_id] = [
                'rfq_vendor_id' => $rfqVendor->id,
                'vendor_id' => $rfqVendor->vendor_id,
                'vendor_name' => $rfqVendor->vendor->vendor_name,
                'invitation_status' => $rfqVendor->invitation_status,
                'quoted_price' => $avgQuotedPrice,
                'total_cost' => round($total, 2),
                'total_price' => round($total, 2),
                'avg_lead_time_days' => $avgLead,
                'payment_terms' => $paymentTerms,
                'delivery_terms' => $deliveryTerms,
                'warranty' => $warranty,
                'supplier_rating' => $metrics['supplier_rating'],
                'historical_performance' => $metrics['performance_percent'],
                'score' => 0,
                'score_breakdown' => [],
                'lines' => $rfq->items->map(function ($item) use ($responses) {
                    $response = $responses->firstWhere('rfq_item_id', $item->id);

                    return [
                        'rfq_item_id' => $item->id,
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'quoted_price' => (float) ($response?->quoted_price ?? 0),
                        'lead_time_days' => $response?->lead_time_days,
                        'warranty' => $response?->warranty,
                        'delivery_terms' => $response?->delivery_terms,
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
        }

        foreach ($vendors as $vendorId => &$row) {
            $breakdown = self::scoreBreakdown(
                $vendorId,
                $priceTotals,
                $leadTimes,
                $performanceScores,
                $qualityScores,
                $weights,
            );
            $row['score'] = $breakdown['total'];
            $row['score_breakdown'] = $breakdown['components'];

            if ($bestScore === null || $breakdown['total'] > $bestScore) {
                $bestScore = $breakdown['total'];
                $bestScoreId = $vendorId;
            }
        }
        unset($row);

        $requiredDate = $rfq->closing_date?->toDateString()
            ?? $rfq->purchaseRequest?->required_date?->toDateString()
            ?? '—';

        return [
            'rfq' => [
                'id' => $rfq->id,
                'rfq_number' => $rfq->rfq_number,
                'status' => $rfq->status->value,
                'required_date' => $requiredDate,
                'purchase_request_number' => $rfq->purchaseRequest?->request_number,
            ],
            'items' => $rfq->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'inventory_item' => $item->inventoryItem?->item_name,
                'quantity' => (float) $item->quantity,
                'required_date' => $requiredDate,
            ])->values()->all(),
            'matrix' => array_values($vendors),
            'highlights' => [
                'lowest_price_vendor_id' => $lowestPriceId,
                'best_lead_time_vendor_id' => $bestLeadId,
                'best_score_vendor_id' => $bestScoreId,
            ],
            'recommended_vendor_id' => $bestScoreId,
            'weights' => $weights,
        ];
    }

    /**
     * @return array{matrix: array<int, array<string, mixed>>, highlights: array<string, int|null>, recommended_vendor_id: int|null}
     */
    public static function buildMatrix(Rfq $rfq): array
    {
        $workspace = self::buildWorkspace($rfq);

        return [
            'matrix' => $workspace['matrix'],
            'highlights' => $workspace['highlights'],
            'recommended_vendor_id' => $workspace['recommended_vendor_id'],
        ];
    }

    /**
     * @param  array<string, int>|null  $weights
     */
    public static function persistComparison(Rfq $rfq, int $userId, ?string $notes = null, ?array $weights = null): VendorComparison
    {
        $built = self::buildWorkspace($rfq, $weights);

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
                    'scoring_weights' => $built['weights'],
                    'created_by' => $userId,
                ],
            );

            if ($rfq->status === RfqStatus::AwaitingComparison) {
                $rfq->update(['status' => RfqStatus::Closed]);
            }

            return $comparison->fresh(['recommendedVendor']);
        });
    }

    /**
     * @param  array<string, int>  $weights
     * @return array<string, int>
     */
    public static function normalizeWeights(array $weights): array
    {
        $defaults = self::defaultWeights();
        $merged = array_merge($defaults, array_intersect_key($weights, $defaults));
        $total = array_sum($merged) ?: 1;

        return collect($merged)
            ->map(fn (int $value) => (int) round(($value / $total) * 100))
            ->all();
    }

    /**
     * @param  array<int, float>  $priceTotals
     * @param  array<int, ?int>  $leadTimes
     * @param  array<int, ?float>  $performanceScores
     * @param  array<int, float>  $qualityScores
     * @param  array<string, int>  $weights
     * @return array{total: int, components: array<string, int>}
     */
    protected static function scoreBreakdown(
        int $vendorId,
        array $priceTotals,
        array $leadTimes,
        array $performanceScores,
        array $qualityScores,
        array $weights,
    ): array {
        $minPrice = min($priceTotals) ?: 1;
        $priceTotal = $priceTotals[$vendorId] ?? $minPrice;
        $priceScore = (int) round(max(0, min(100, ($minPrice / max($priceTotal, 0.01)) * 100)));

        $validLeads = collect($leadTimes)->filter(fn ($days) => $days !== null);
        $bestLead = $validLeads->min() ?? 0;
        $lead = $leadTimes[$vendorId] ?? null;
        $leadScore = $lead === null
            ? 50
            : (int) round(max(0, min(100, 100 - (($lead - $bestLead) * 5))));

        $performance = $performanceScores[$vendorId];
        $performanceScore = $performance === null
            ? 50
            : (int) round(max(0, min(100, $performance)));

        $reliabilityBlend = (float) config('procurement_scoring.reliability_blend', 0.5);
        $performanceScore = (int) round(
            ($performanceScore * (1 - $reliabilityBlend)) + ($performanceScore * $reliabilityBlend)
        );

        $qualityScore = (int) round(max(0, min(100, $qualityScores[$vendorId] ?? 0)));

        $components = [
            'price' => (int) round($priceScore * ($weights['price'] / 100)),
            'performance' => (int) round($performanceScore * ($weights['performance'] / 100)),
            'lead_time' => (int) round($leadScore * ($weights['lead_time'] / 100)),
            'quality' => (int) round($qualityScore * ($weights['quality'] / 100)),
        ];

        return [
            'total' => array_sum($components),
            'components' => $components,
        ];
    }
}
