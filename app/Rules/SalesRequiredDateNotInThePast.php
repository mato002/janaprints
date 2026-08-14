<?php

namespace App\Rules;

use App\Models\Sales\SalesOrder;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class SalesRequiredDateNotInThePast implements ValidationRule
{
    public function __construct(protected ?SalesOrder $existing = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $date = Carbon::parse($value)->startOfDay();
        $stored = $this->existing?->required_date?->copy()->startOfDay();

        if ($stored?->equalTo($date)) {
            return;
        }

        if ($date->lt(today())) {
            $fail(__('The required date cannot be before today.'));
        }
    }
}
