<?php

namespace App\Support\Assets;

use App\Enums\DepreciationMethod;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepreciationMethodGovernance
{
    /**
     * @return list<\Illuminate\Validation\Rules\Enum|string>
     */
    public static function selectionRules(bool $required = false): array
    {
        $rules = [Rule::in(DepreciationMethod::selectableValues())];

        return $required ? ['required', ...$rules] : ['nullable', ...$rules];
    }

    public static function assertSelectable(?string $value, string $attribute = 'depreciation_method'): DepreciationMethod
    {
        $method = DepreciationMethod::tryFromSelectable($value);

        if ($method === null) {
            throw ValidationException::withMessages([
                $attribute => __('The selected depreciation method is not available. Only :methods are supported.', [
                    'methods' => collect(DepreciationMethod::selectableCases())
                        ->map(fn (DepreciationMethod $case) => $case->label())
                        ->join(', '),
                ]),
            ]);
        }

        return $method;
    }

    public static function sanitizeValue(?string $value): string
    {
        return DepreciationMethod::sanitizeValue($value);
    }
}
