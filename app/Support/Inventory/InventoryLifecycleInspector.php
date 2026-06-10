<?php

namespace App\Support\Inventory;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\InventoryMovementType;
use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\ProductionOutput;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\ProductionMaterialConsumptionService;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;

class InventoryLifecycleInspector
{
    /**
     * @return array{
     *     checks: list<array{key: string, label: string, status: string, detail: string}>,
     *     passed: int,
     *     failed: int,
     *     score: string
     * }
     */
    public function inspect(): array
    {
        $checks = [
            $this->checkFgDuplicateProtection(),
            $this->checkTransitCancellationGovernance(),
            $this->checkWipPostingExclusivity(),
            $this->checkFgMovementIdempotency(),
            $this->checkJournalIntegrity(),
            $this->checkInventoryTruthSource(),
            $this->checkWipArchitectureDocumented(),
        ];

        $passed = collect($checks)->where('status', 'PASS')->count();
        $failed = collect($checks)->where('status', 'FAIL')->count();

        return [
            'checks' => $checks,
            'passed' => $passed,
            'failed' => $failed,
            'score' => sprintf('%d/%d PASS', $passed, count($checks)),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkFgDuplicateProtection(): array
    {
        $configOk = (bool) config('inventory_lifecycle.production_completion.one_posted_output_per_job', false);
        $columnOk = Schema::hasColumn('production_outputs', 'posted_job_marker');
        $indexOk = $this->hasIndex('production_outputs', 'prod_outputs_one_posted_per_job');

        $ok = $configOk && $columnOk && $indexOk;

        return [
            'key' => 'fg_duplicate_protection',
            'label' => __('FG duplicate protection (one posted output per job)'),
            'status' => $ok ? 'PASS' : 'FAIL',
            'detail' => $ok
                ? __('posted_job_marker column and unique index present; config enforced.')
                : __('Missing posted_job_marker column, unique index, or lifecycle config.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkTransitCancellationGovernance(): array
    {
        $draftOnly = DeliveryNoteStatus::Draft->canCancel()
            && ! DeliveryNoteStatus::Dispatched->canCancel()
            && ! DeliveryNoteStatus::Delivered->canCancel();
        $configOk = config('inventory_lifecycle.dispatch.cancel_allowed_statuses') === ['draft'];

        $ok = $draftOnly && $configOk;

        return [
            'key' => 'transit_cancellation_governance',
            'label' => __('Transit cancellation governance'),
            'status' => $ok ? 'PASS' : 'FAIL',
            'detail' => $ok
                ? __('Only draft delivery notes can be cancelled.')
                : __('Dispatched or delivered notes can still be cancelled.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkWipPostingExclusivity(): array
    {
        $configOk = config('inventory_lifecycle.wip.wip_posting_source') === 'production_material_consumption'
            && config('inventory_lifecycle.wip.stock_issue_production_posts_wip') === false;

        $method = new ReflectionMethod(InventoryAccountingPostingService::class, 'postStockIssue');
        $source = file_get_contents($method->getFileName()) ?: '';
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $lines = implode("\n", array_slice(explode("\n", $source), $start - 1, $end - $start + 1));
        $codeOk = str_contains($lines, 'StockIssueDestination::Production')
            && str_contains($lines, 'return null');

        $consumptionMethod = new \ReflectionMethod(ProductionMaterialConsumptionService::class, 'consume');
        $consumptionDoc = $consumptionMethod->getDocComment() ?: '';
        $docOk = str_contains(strtolower($consumptionDoc), 'sole')
            && str_contains(strtolower($consumptionDoc), 'wip');

        $ok = $configOk && $codeOk && $docOk;

        return [
            'key' => 'wip_posting_exclusivity',
            'label' => __('WIP posting exclusivity'),
            'status' => $ok ? 'PASS' : 'FAIL',
            'detail' => $ok
                ? __('Stock issue → production skips WIP journal; job consumption is documented as sole WIP source.')
                : __('Production stock issues may still post WIP or consumption source is undocumented.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkFgMovementIdempotency(): array
    {
        $columnOk = Schema::hasColumn('inventory_movements', 'lifecycle_receipt_key');
        $indexOk = $this->hasIndex('inventory_movements', 'inv_movements_lifecycle_receipt_unique');

        $duplicateFgMovements = InventoryMovement::query()
            ->where('movement_type', InventoryMovementType::FinishedGoodsReceipt)
            ->where('reference_type', ProductionOutput::class)
            ->select('reference_id')
            ->groupBy('reference_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $ok = $columnOk && $indexOk && $duplicateFgMovements === 0;

        return [
            'key' => 'fg_movement_idempotency',
            'label' => __('FG receipt movement idempotency'),
            'status' => $ok ? 'PASS' : 'FAIL',
            'detail' => $ok
                ? __('lifecycle_receipt_key unique index present; no duplicate FG receipts detected.')
                : __('Missing lifecycle_receipt_key protection or duplicate FG receipt movements exist.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkJournalIntegrity(): array
    {
        $indexOk = $this->hasIndex('journals', 'journals_source_posting_unique');

        return [
            'key' => 'journal_integrity',
            'label' => __('Journal posting integrity'),
            'status' => $indexOk ? 'PASS' : 'FAIL',
            'detail' => $indexOk
                ? __('Unique index on (company, posting_event, source_type, source_id) present.')
                : __('Missing journals_source_posting_unique index.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkInventoryTruthSource(): array
    {
        $stages = config('inventory_lifecycle.inventory_stages', []);
        $expected = ['raw_materials', 'finished_goods', 'in_transit', 'delivered'];
        $ok = $stages === $expected && ! in_array('wip', $stages, true);

        return [
            'key' => 'inventory_truth_source',
            'label' => __('Inventory truth source'),
            'status' => $ok ? 'PASS' : 'FAIL',
            'detail' => $ok
                ? __('Inventory lifecycle excludes WIP quantity; truth remains inventory_movements.')
                : __('Inventory lifecycle config is missing or includes WIP as a quantity stage.'),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function checkWipArchitectureDocumented(): array
    {
        $configOk = (bool) config('inventory_lifecycle.wip.accounting_only', false);
        $enumOk = VirtualWarehouseRole::Wip->isAccountingOnlyLayer()
            && ! VirtualWarehouseRole::Wip->tracksPhysicalInventory();

        return [
            'key' => 'wip_architecture',
            'label' => __('WIP accounting-only architecture'),
            'status' => ($configOk && $enumOk) ? 'PASS' : 'FAIL',
            'detail' => ($configOk && $enumOk)
                ? __('WIP documented as accounting-only; virtual WIP warehouse reserved for future use.')
                : __('WIP architecture flags are incomplete.'),
        ];
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? null) === $indexName);
        }

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $indexes = $connection->select(
                'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$database, $table, $indexName],
            );

            return count($indexes) > 0;
        }

        return Schema::hasColumn($table, match ($indexName) {
            'prod_outputs_one_posted_per_job' => 'posted_job_marker',
            'inv_movements_lifecycle_receipt_unique' => 'lifecycle_receipt_key',
            default => 'id',
        });
    }
}
