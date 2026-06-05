<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ClientLoginRequest extends LoginRequest
{
    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        parent::authenticate();

        $user = Auth::user();

        if ($user && ! $user->isClientPortalAccount()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('This account is for Jana Prints staff. Please use the ERP sign in.'),
            ]);
        }
    }
}
