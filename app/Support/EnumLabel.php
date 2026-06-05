<?php

namespace App\Support;

use BackedEnum;
use UnitEnum;

final class EnumLabel
{
    public static function of(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_object($value) && method_exists($value, 'label')) {
            return $value->label();
        }

        if ($value instanceof BackedEnum) {
            return ucfirst(str_replace('_', ' ', (string) $value->value));
        }

        if ($value instanceof UnitEnum) {
            return ucfirst(strtolower($value->name));
        }

        return (string) $value;
    }
}
