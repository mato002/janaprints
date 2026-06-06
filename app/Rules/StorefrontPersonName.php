<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StorefrontPersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = trim((string) $value);

        if ($name === '') {
            return;
        }

        if (! preg_match("/^[\p{L}\s'.-]+$/u", $name)) {
            $fail(__('The :attribute may only contain letters, spaces, hyphens, apostrophes, and periods.'));

            return;
        }

        if (preg_match('/\d/', $name)) {
            $fail(__('The :attribute may not contain numbers.'));
        }
    }
}
