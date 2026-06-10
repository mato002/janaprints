<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Carbon;

class ProfitabilitySnapshotGeneratorService
{
    public function __construct(
        protected ProductionProfitabilityService $profitability,
    ) {}

    /**
     * @return list<PrintProfitabilitySnapshot>
     */
    public function generateForCompany(int $companyId, int $days = 90, ?string $snapshotType = null, bool $persist = true): array
    {
        if (! config('printing_intelligence.profitability_intelligence_enabled', true)) {
            return [];
        }

        $since = now()->subDays($days);
        $snapshots = [];

        if ($snapshotType === null || $snapshotType === 'job') {
            $jobs = ProductionJobCard::query()
                ->where('company_id', $companyId)
                ->where('updated_at', '>=', $since)
                ->with(['salesOrder', 'quotation', 'customer'])
                ->get();

            foreach ($jobs as $job) {
                $snapshot = $this->generateJobSnapshot($job, $persist);
                if ($snapshot !== null) {
                    $snapshots[] = $snapshot;
                }
            }
        }

        if ($snapshotType === null || in_array($snapshotType, ['customer', 'machine', 'product', 'period'], true)) {
            $jobSnapshots = PrintProfitabilitySnapshot::query()
                ->where('company_id', $companyId)
                ->where('snapshot_type', ProfitabilitySnapshotType::Job)
                ->where('snapshot_date', '>=', $since->toDateString())
                ->get();

            if ($snapshotType === null || $snapshotType === 'customer') {
                $snapshots = array_merge($snapshots, $this->aggregateByCustomer($companyId, $jobSnapshots, $persist));
            }
            if ($snapshotType === null || $snapshotType === 'machine') {
                $snapshots = array_merge($snapshots, $this->aggregateByMachine($companyId, $jobSnapshots, $persist));
            }
            if ($snapshotType === null || $snapshotType === 'product') {
                $snapshots = array_merge($snapshots, $this->aggregateByProduct($companyId, $jobSnapshots, $persist));
            }
            if ($snapshotType === null || $snapshotType === 'period') {
                $snapshots = array_merge($snapshots, $this->aggregateByPeriod($companyId, $jobSnapshots, $persist));
            }
        }

        return $snapshots;
    }

    public function generateJobSnapshot(ProductionJobCard $jobCard, bool $persist = true): ?PrintProfitabilitySnapshot
    {
        $metrics = $this->profitability->calculateForJob($jobCard);

        if ((float) $metrics['total_cost'] <= 0 && (float) $metrics['revenue'] <= 0) {
            return null;
        }

        $date = ($jobCard->actual_end_date ?? $jobCard->updated_at)?->toDateString() ?? now()->toDateString();
        $payload = array_merge($metrics, [
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'snapshot_date' => $date,
        ]);

        if (! $persist) {
            return new PrintProfitabilitySnapshot($payload);
        }

        return PrintProfitabilitySnapshot::query()->updateOrCreate(
            [
                'company_id' => $jobCard->company_id,
                'snapshot_type' => ProfitabilitySnapshotType::Job,
                'production_job_card_id' => $jobCard->id,
                'snapshot_date' => $date,
            ],
            $payload,
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintProfitabilitySnapshot>  $jobSnapshots
     * @return list<PrintProfitabilitySnapshot>
     */
    protected function aggregateByCustomer(int $companyId, $jobSnapshots, bool $persist): array
    {
        $results = [];
        $grouped = $jobSnapshots->groupBy('customer_id');

        foreach ($grouped as $customerId => $rows) {
            if (! $customerId) {
                continue;
            }
            $results[] = $this->persistAggregate($companyId, ProfitabilitySnapshotType::Customer, [
                'customer_id' => $customerId,
            ], $rows, $persist);
        }

        return array_filter($results);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintProfitabilitySnapshot>  $jobSnapshots
     * @return list<PrintProfitabilitySnapshot>
     */
    protected function aggregateByMachine(int $companyId, $jobSnapshots, bool $persist): array
    {
        $results = [];
        $grouped = $jobSnapshots->groupBy('machine_profile_id');

        foreach ($grouped as $machineId => $rows) {
            if (! $machineId) {
                continue;
            }
            $results[] = $this->persistAggregate($companyId, ProfitabilitySnapshotType::Machine, [
                'machine_profile_id' => $machineId,
            ], $rows, $persist);
        }

        return array_filter($results);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintProfitabilitySnapshot>  $jobSnapshots
     * @return list<PrintProfitabilitySnapshot>
     */
    protected function aggregateByProduct(int $companyId, $jobSnapshots, bool $persist): array
    {
        $results = [];
        $grouped = $jobSnapshots->groupBy(fn ($row) => $row->metadata['production_type'] ?? 'unknown');

        foreach ($grouped as $productKey => $rows) {
            $results[] = $this->persistAggregate($companyId, ProfitabilitySnapshotType::Product, [], $rows, $persist, [
                'product_key' => $productKey,
                'product_label' => config("printing_intelligence.product_type_labels.{$productKey}", ucfirst(str_replace('_', ' ', $productKey))),
            ]);
        }

        return array_filter($results);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintProfitabilitySnapshot>  $jobSnapshots
     * @return list<PrintProfitabilitySnapshot>
     */
    protected function aggregateByPeriod(int $companyId, $jobSnapshots, bool $persist): array
    {
        $results = [];
        $grouped = $jobSnapshots->groupBy(fn ($row) => Carbon::parse($row->snapshot_date)->format('Y-m'));

        foreach ($grouped as $period => $rows) {
            $results[] = $this->persistAggregate($companyId, ProfitabilitySnapshotType::Period, [], $rows, $persist, [
                'period' => $period,
            ], Carbon::parse($period.'-01')->toDateString());
        }

        return array_filter($results);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintProfitabilitySnapshot>  $rows
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $extraMeta
     */
    protected function persistAggregate(
        int $companyId,
        ProfitabilitySnapshotType $type,
        array $keys,
        $rows,
        bool $persist,
        array $extraMeta = [],
        ?string $snapshotDate = null,
    ): ?PrintProfitabilitySnapshot {
        if ($rows->isEmpty()) {
            return null;
        }

        $revenue = round($rows->sum(fn ($r) => (float) $r->revenue), 2);
        $totalCost = round($rows->sum(fn ($r) => (float) $r->total_cost), 2);
        $grossProfit = round($revenue - $totalCost, 2);
        $margin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 3) : null;
        $date = $snapshotDate ?? now()->toDateString();

        $payload = [
            'company_id' => $companyId,
            'snapshot_type' => $type,
            'revenue' => $revenue,
            'material_cost' => round($rows->sum(fn ($r) => (float) $r->material_cost), 2),
            'ink_cost' => round($rows->sum(fn ($r) => (float) $r->ink_cost), 2),
            'machine_cost' => round($rows->sum(fn ($r) => (float) $r->machine_cost), 2),
            'labour_cost' => round($rows->sum(fn ($r) => (float) $r->labour_cost), 2),
            'overhead_cost' => round($rows->sum(fn ($r) => (float) $r->overhead_cost), 2),
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'gross_margin_percent' => $margin,
            'profitability_class' => app(ProductionProfitabilityService::class)->classify($margin),
            'profitability_score' => $margin,
            'snapshot_date' => $date,
            'metadata' => array_merge($extraMeta, ['job_count' => $rows->count()]),
        ] + $keys;

        if (! $persist) {
            return new PrintProfitabilitySnapshot($payload);
        }

        $query = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', $type)
            ->where('snapshot_date', $date);

        if (isset($keys['customer_id'])) {
            $query->where('customer_id', $keys['customer_id']);
        }
        if (isset($keys['machine_profile_id'])) {
            $query->where('machine_profile_id', $keys['machine_profile_id']);
        }
        if ($type === ProfitabilitySnapshotType::Product && isset($extraMeta['product_key'])) {
            $query->where('metadata->product_key', $extraMeta['product_key']);
        }

        $query->delete();

        return PrintProfitabilitySnapshot::query()->create($payload);
    }
}
