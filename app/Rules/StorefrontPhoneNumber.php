<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StorefrontPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = trim((string) $value);

        if ($phone === '') {
            return;
        }

        if (! preg_match('/^[\d\s+().-]+$/', $phone)) {
            $fail(__('The :attribute may only contain numbers, spaces, and + ( ) - .'));

            return;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        $length = strlen($digits);

        if ($length < 9 || $length > 15) {
            $fail(__('The :attribute must contain between 9 and 15 digits.'));
        }
    }
}
