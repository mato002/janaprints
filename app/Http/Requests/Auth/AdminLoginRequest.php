<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends LoginRequest
{
    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        parent::authenticate();

        $user = Auth::user();

        if ($user && ! $user->isStaffAccount()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('This account is for client portal access. Please use the client login page.'),
            ]);
        }
    }
}
