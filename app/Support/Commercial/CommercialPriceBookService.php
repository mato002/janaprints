<?php

namespace App\Support\Commercial;

use App\Enums\CommercialPriceBookStatus;
use App\Models\Commercial\CommercialCustomerPriceBook;
use App\Models\Commercial\CommercialPriceBook;
use App\Models\Commercial\CommercialPriceBookItem;
use App\Models\Inventory\InventoryItem;
use Illuminate\Support\Carbon;

class CommercialPriceBookService
{
    public function resolveForCustomer(?int $customerId, int $companyId, ?int $branchId = null): ?CommercialPriceBook
    {
        if ($customerId) {
            $assignment = CommercialCustomerPriceBook::query()
                ->where('company_id', $companyId)
                ->where('customer_id', $customerId)
                ->where('status', CommercialPriceBookStatus::Active)
                ->where(function ($q): void {
                    $now = now();
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q): void {
                    $now = now();
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                })
                ->with('priceBook')
                ->latest('id')
                ->first();

            if ($assignment?->priceBook && $this->isBookActive($assignment->priceBook)) {
                return $assignment->priceBook;
            }
        }

        return $this->resolveDefault($companyId, $branchId);
    }

    public function resolveDefault(int $companyId, ?int $branchId = null): ?CommercialPriceBook
    {
        $query = CommercialPriceBook::query()
            ->where('company_id', $companyId)
            ->where('status', CommercialPriceBookStatus::Active)
            ->where('is_default', true);

        if ($branchId) {
            $book = (clone $query)->where('branch_id', $branchId)->first();
            if ($book && $this->isBookActive($book)) {
                return $book;
            }
        }

        $book = (clone $query)->whereNull('branch_id')->first();

        return $book && $this->isBookActive($book) ? $book : null;
    }

    public function resolveItemPrice(
        ?int $customerId,
        int $itemId,
        int $companyId,
        ?int $branchId = null,
        float $quantity = 1.0,
    ): ?float {
        $book = $this->resolveForCustomer($customerId, $companyId, $branchId);

        if (! $book) {
            return null;
        }

        $item = CommercialPriceBookItem::query()
            ->where('price_book_id', $book->id)
            ->where('inventory_item_id', $itemId)
            ->where('status', CommercialPriceBookStatus::Active)
            ->where(function ($q): void {
                $now = now();
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $now);
            })
            ->where(function ($q): void {
                $now = now();
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->orderByDesc('minimum_quantity')
            ->get()
            ->first(fn (CommercialPriceBookItem $row) => $row->minimum_quantity === null || (float) $row->minimum_quantity <= $quantity);

        if (! $item) {
            return null;
        }

        $price = (float) $item->unit_price;

        if ($item->discount_percent) {
            $price -= $price * ((float) $item->discount_percent / 100);
        }

        return round($price, 2);
    }

    public function resolveInventoryFallbackPrice(InventoryItem $item, ?int $customerId, int $companyId, ?int $branchId, float $quantity = 1.0): float
    {
        $bookPrice = $this->resolveItemPrice($customerId, (int) $item->id, $companyId, $branchId, $quantity);

        return $bookPrice ?? (float) $item->standard_cost;
    }

    public function assignCustomerPriceBook(
        int $companyId,
        int $customerId,
        int $priceBookId,
        ?Carbon $startsAt = null,
        ?Carbon $endsAt = null,
    ): CommercialCustomerPriceBook {
        CommercialCustomerPriceBook::query()
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', CommercialPriceBookStatus::Active)
            ->update(['status' => CommercialPriceBookStatus::Inactive]);

        return CommercialCustomerPriceBook::query()->create([
            'company_id' => $companyId,
            'customer_id' => $customerId,
            'price_book_id' => $priceBookId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => CommercialPriceBookStatus::Active,
        ]);
    }

    public function setAsDefault(CommercialPriceBook $book): void
    {
        CommercialPriceBook::query()
            ->where('company_id', $book->company_id)
            ->when($book->branch_id, fn ($q) => $q->where('branch_id', $book->branch_id), fn ($q) => $q->whereNull('branch_id'))
            ->where('id', '!=', $book->id)
            ->update(['is_default' => false]);

        $book->update(['is_default' => true, 'status' => CommercialPriceBookStatus::Active]);
    }

    protected function isBookActive(CommercialPriceBook $book): bool
    {
        if ($book->status !== CommercialPriceBookStatus::Active) {
            return false;
        }

        $now = now();

        if ($book->starts_at && $book->starts_at->isFuture()) {
            return false;
        }

        if ($book->ends_at && $book->ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
