<?php

namespace App\Enums;

enum CustomerPrintSpecificationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Superseded = 'superseded';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Superseded => __('Superseded'),
            self::Archived => __('Archived'),
        };
    }

    public function isSelectableForOrders(): bool
    {
        return $this === self::Active;
    }

    public function isReadOnly(): bool
    {
        return $this === self::Archived;
    }

    public function isHiddenFromOrderSelection(): bool
    {
        return ! $this->isSelectableForOrders();
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::Archived],
            self::Active => [self::Superseded, self::Archived],
            self::Superseded => [self::Active, self::Archived],
            self::Archived => [],
        };
    }
}
