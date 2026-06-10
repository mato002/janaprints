<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Casts known enum values to the enum; unknown values remain strings.
 *
 * @implements CastsAttributes<UnitEnum|string|null, UnitEnum|string|null>
 */
class FlexibleEnumCast implements CastsAttributes
{
    public function __construct(
        protected string $enumClass,
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): UnitEnum|string|null
    {
        if ($value === null) {
            return null;
        }

        $enum = $this->enumClass::tryFrom((string) $value);

        return $enum ?? (string) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UnitEnum) {
            return $value->value;
        }

        return (string) $value;
    }
}
