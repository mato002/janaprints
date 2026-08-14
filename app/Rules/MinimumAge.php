<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class MinimumAge implements ValidationRule
{
    public function __construct(
        protected int $years = 18,
        protected int $maxYears = 100,
        protected mixed $existingValue = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $dob = Carbon::parse($value)->startOfDay();

        if (filled($this->existingValue) && Carbon::parse($this->existingValue)->startOfDay()->equalTo($dob)) {
            return;
        }

        if ($dob->gt(today())) {
            $fail(__('The date of birth cannot be in the future.'));

            return;
        }

        if ($dob->gt(today()->subYears($this->years))) {
            $fail(__('Must be at least :years years old.', ['years' => $this->years]));

            return;
        }

        if ($dob->lt(today()->subYears($this->maxYears))) {
            $fail(__('Enter a valid date of birth.'));
        }
    }
}
