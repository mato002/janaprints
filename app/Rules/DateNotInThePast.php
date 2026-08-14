<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class DateNotInThePast implements ValidationRule
{
    public function __construct(protected mixed $existingValue = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $date = Carbon::parse($value)->startOfDay();
        $stored = filled($this->existingValue)
            ? Carbon::parse($this->existingValue)->startOfDay()
            : null;

        if ($stored?->equalTo($date)) {
            return;
        }

        if ($date->lt(today())) {
            $fail(__('This date cannot be before today.'));
        }
    }
}
