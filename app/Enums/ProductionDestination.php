<?php

namespace App\Enums;

enum ProductionDestination: string
{
    case Digital = 'digital';
    case Offset = 'offset';
    case Outsource = 'outsource';

    public function label(): string
    {
        return match ($this) {
            self::Digital => __('Digital'),
            self::Offset => __('Offset'),
            self::Outsource => __('Outsourced'),
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Digital => __('In-house digital press'),
            self::Offset => __('In-house offset / job cards'),
            self::Outsource => __('Send to an external vendor'),
        };
    }

    public function productionType(): ?ProductionType
    {
        return match ($this) {
            self::Digital => ProductionType::Digital,
            self::Offset => ProductionType::Offset,
            self::Outsource => null,
        };
    }

    public function sendToLabel(): string
    {
        return match ($this) {
            self::Digital => __('Send to Digital'),
            self::Offset => __('Send to Offset'),
            self::Outsource => __('Send to Outsourced'),
        };
    }

    public function isOutsource(): bool
    {
        return $this === self::Outsource;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
