<?php

namespace App\Enums;

enum InventoryPressProcess: string
{
    case Digital = 'digital';
    case Offset = 'offset';
    case Shared = 'shared';
    case Outsource = 'outsource';

    public function label(): string
    {
        return match ($this) {
            self::Digital => __('Digital'),
            self::Offset => __('Offset'),
            self::Shared => __('Shared'),
            self::Outsource => __('Outsourced'),
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Digital => __('Used on in-house digital jobs.'),
            self::Offset => __('Used on in-house offset jobs.'),
            self::Shared => __('Used on both digital and offset.'),
            self::Outsource => __('Held for outsourced / vendor jobs.'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Digital => 'bg-indigo-50 text-indigo-900',
            self::Offset => 'bg-teal-50 text-teal-900',
            self::Shared => 'bg-slate-100 text-slate-800',
            self::Outsource => 'bg-amber-50 text-amber-950',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
