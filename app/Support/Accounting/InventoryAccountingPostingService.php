<?php

namespace App\Support\Accounting;

use App\Enums\PostingEventCode;
use App\Enums\StockIssueDestination;
use App\Enums\StockReceiptSource;
use App\Models\Accounting\Journal;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Production\ProductionOutput;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InventoryAccountingPostingService
{
    public function __construct(
        protected AccountingPostingService $posting,
    ) {}

    public function postStockReceipt(StockReceipt $receipt, int $userId): ?Journal
    {
        if ($receipt->source === StockReceiptSource::Purchase) {
            return null;
        }

        $total = $this->lineTotal($receipt->items);

        if ($total <= 0) {
            return null;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::InventoryReceiptPosted,
            $receipt->company_id,
            $userId,
            'stock_receipt',
            $receipt->id,
            $receipt->receipt_date->toDateString(),
            ['total_amount' => $total],
            $receipt->branch_id,
            reference: $receipt->receipt_number,
            description: __('Inventory receipt :number', ['number' => $receipt->receipt_number]),
        );

        $receipt->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    /**
     * @param  Collection<int, mixed>|null  $lines
     */
    public function postGoodsReceipt(GoodsReceipt $goodsReceipt, int $userId, ?Collection $lines = null): ?Journal
    {
        $goodsReceipt->load('items');
        $lines ??= $goodsReceipt->items;
        $total = $this->lineTotal($lines, 'quantity_received', 'unit_cost');

        if ($total <= 0) {
            return null;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::ProcurementGoodsReceiptPosted,
            $goodsReceipt->company_id,
            $userId,
            'goods_receipt',
            $goodsReceipt->id,
            $goodsReceipt->receipt_date->toDateString(),
            ['total_amount' => $total],
            $goodsReceipt->branch_id,
            reference: $goodsReceipt->receipt_number,
            description: __('Goods receipt :number', ['number' => $goodsReceipt->receipt_number]),
        );

        $goodsReceipt->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postStockIssue(StockIssue $issue, int $userId): ?Journal
    {
        if ($issue->destination === StockIssueDestination::Transfer) {
            return null;
        }

        // Phase I4.1: production stock issues are operational/reservation only.
        // WIP accounting (Dr WIP / Cr Raw Materials) is exclusive to ProductionMaterialConsumptionService.
        if ($issue->destination === StockIssueDestination::Production) {
            return null;
        }

        $issue->load('items');
        $total = $this->lineTotal($issue->items);

        if ($total <= 0) {
            return null;
        }

        $event = match ($issue->destination) {
            StockIssueDestination::InternalUse,
            StockIssueDestination::Damage => PostingEventCode::InventoryConsumptionPosted,
        };

        $journal = $this->posting->postEvent(
            $event,
            $issue->company_id,
            $userId,
            'stock_issue',
            $issue->id,
            $issue->issue_date->toDateString(),
            ['total_amount' => $total],
            $issue->branch_id,
            reference: $issue->issue_number,
            description: __('Stock issue :number', ['number' => $issue->issue_number]),
        );

        $issue->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postMaterialConsumption(ProductionMaterialConsumption $consumption, int $userId): ?Journal
    {
        // Sole source of Dr WIP / Cr Raw Materials for production jobs (Phase I4.1).
        // Stock issues to production do not post WIP accounting.
        $total = round((float) $consumption->quantity * (float) $consumption->unit_cost, 2);

        if ($total <= 0) {
            return null;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::ProductionMaterialConsumptionPosted,
            $consumption->company_id,
            $userId,
            'production_material_consumption',
            $consumption->id,
            $consumption->consumed_at?->toDateString() ?? now()->toDateString(),
            ['total_amount' => $total],
            $consumption->branch_id,
            reference: __('Job consumption #:id', ['id' => $consumption->id]),
            description: __('Production material consumption'),
        );

        $consumption->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postProductionCompletion(ProductionOutput $output, int $userId): Journal
    {
        $output->loadMissing('jobCard');

        $total = round((float) $output->total_cost, 2);

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'total_cost' => __('Production completion total cost must be greater than zero.'),
            ]);
        }

        $existing = $this->posting->findPostedJournal(new \App\Support\Accounting\Dto\PostingContext(
            companyId: $output->company_id,
            userId: $userId,
            event: PostingEventCode::ProductionCompletionPosted,
            sourceType: 'production_output',
            sourceId: $output->id,
            journalDate: $output->completed_at?->toDateString() ?? now()->toDateString(),
            branchId: $output->branch_id,
        ));

        if ($existing) {
            $output->update(['posted_journal_id' => $existing->id]);

            return $existing;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::ProductionCompletionPosted,
            $output->company_id,
            $userId,
            'production_output',
            $output->id,
            now()->toDateString(),
            ['total_amount' => $total],
            $output->branch_id,
            reference: $output->jobCard?->job_card_number ?? __('Output #:id', ['id' => $output->id]),
            description: __('Production completion to finished goods'),
        );

        $output->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postDeliveryCogs(DeliveryNote $note, int $userId): Journal
    {
        $note->loadMissing('items');

        $total = round($note->items->sum(fn ($line) => (float) ($line->total_cost ?? 0)), 2);

        if ($total <= 0) {
            $total = round($note->items->sum(fn ($line) => (float) $line->quantity * (float) $line->unit_cost), 2);
        }

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'total_cost' => __('Delivery COGS total must be greater than zero.'),
            ]);
        }

        $existing = $this->posting->findPostedJournal(new \App\Support\Accounting\Dto\PostingContext(
            companyId: $note->company_id,
            userId: $userId,
            event: PostingEventCode::DeliveryCogsPosted,
            sourceType: 'delivery_note',
            sourceId: $note->id,
            journalDate: $note->delivered_at?->toDateString() ?? now()->toDateString(),
            branchId: $note->branch_id,
        ));

        if ($existing) {
            $note->update(['posted_journal_id' => $existing->id]);

            return $existing;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::DeliveryCogsPosted,
            $note->company_id,
            $userId,
            'delivery_note',
            $note->id,
            now()->toDateString(),
            ['total_amount' => $total],
            $note->branch_id,
            reference: $note->delivery_note_number,
            description: __('Delivery COGS — :number', ['number' => $note->delivery_note_number]),
        );

        $note->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postStockAdjustment(StockAdjustment $adjustment, int $userId): ?Journal
    {
        $adjustment->load('items');
        $total = 0.0;

        foreach ($adjustment->items as $line) {
            $total += abs((float) $line->quantity) * (float) $line->unit_cost;
        }

        $total = round($total, 2);

        if ($total <= 0) {
            return null;
        }

        $journal = $this->posting->postEvent(
            PostingEventCode::InventoryAdjustmentPosted,
            $adjustment->company_id,
            $userId,
            'stock_adjustment',
            $adjustment->id,
            $adjustment->adjustment_date->toDateString(),
            ['amount' => $total],
            $adjustment->branch_id,
            reference: $adjustment->adjustment_number,
            description: __('Stock adjustment :number', ['number' => $adjustment->adjustment_number]),
        );

        $adjustment->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    /**
     * @param  Collection<int, mixed>  $lines
     */
    protected function lineTotal(
        Collection $lines,
        string $qtyField = 'quantity',
        string $costField = 'unit_cost',
    ): float {
        return round($lines->sum(fn ($line) => (float) $line->{$qtyField} * (float) $line->{$costField}), 2);
    }
}
