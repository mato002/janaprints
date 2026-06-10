<?php

namespace App\Enums;

enum VirtualWarehouseRole: string
{
    case RawMaterial = 'raw_material';
    case Wip = 'wip';
    case FinishedGoods = 'finished_goods';
    case InTransit = 'in_transit';
    case Quarantine = 'quarantine';
    case Adjustment = 'adjustment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::RawMaterial => __('Raw materials'),
            self::Wip => __('Work in progress'),
            self::FinishedGoods => __('Finished goods'),
            self::InTransit => __('In transit'),
            self::Quarantine => __('Quarantine'),
            self::Adjustment => __('Adjustment'),
            self::Other => __('Other'),
        };
    }

    public function defaultCode(): string
    {
        return match ($this) {
            self::RawMaterial => 'VIRTUAL-RAW',
            self::Wip => 'VIRTUAL-WIP',
            self::FinishedGoods => 'VIRTUAL-FG',
            self::InTransit => 'VIRTUAL-TRANSIT',
            self::Quarantine => 'VIRTUAL-QUARANTINE',
            self::Adjustment => 'VIRTUAL-ADJ',
            self::Other => 'VIRTUAL-OTHER',
        };
    }

    public function defaultName(): string
    {
        return match ($this) {
            self::RawMaterial => __('Raw Materials (Virtual)'),
            self::Wip => __('Work In Progress (Virtual)'),
            self::FinishedGoods => __('Finished Goods (Virtual)'),
            self::InTransit => __('In Transit (Virtual)'),
            self::Quarantine => __('Quarantine / Variance (Virtual)'),
            self::Adjustment => __('Adjustment (Virtual)'),
            self::Other => __('Other (Virtual)'),
        };
    }

    public function emptyStateMessage(): ?string
    {
        return match ($this) {
            self::Wip => __('Accounting-only layer. WIP is posted in the general ledger via job material consumption—not tracked as inventory quantity here.'),
            self::FinishedGoods => __('Finished goods appear after production completion is posted.'),
            self::InTransit => null,
            self::Quarantine => __('Quarantine stock will appear when variances are routed here.'),
            default => null,
        };
    }

    /**
     * Virtual roles that exist for future use or accounting alignment but do not receive production inventory movements.
     */
    public function isAccountingOnlyLayer(): bool
    {
        return $this === self::Wip;
    }

    /**
     * Whether quantity balances for this role are expected in the inventory subledger.
     */
    public function tracksPhysicalInventory(): bool
    {
        return ! $this->isAccountingOnlyLayer();
    }

    /**
     * @return list<self>
     */
    public static function seededRoles(): array
    {
        return [
            self::RawMaterial,
            self::Wip,
            self::FinishedGoods,
            self::InTransit,
            self::Quarantine,
        ];
    }

    public function blocksDirectReceipt(): bool
    {
        return match ($this) {
            self::Wip, self::FinishedGoods, self::InTransit, self::Quarantine => true,
            default => false,
        };
    }
}
