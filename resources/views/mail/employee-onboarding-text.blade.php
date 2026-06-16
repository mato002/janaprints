{{ __('Welcome to :company', ['company' => $companyName ?? 'Jana Prints']) }}

{{ __('Hello :name,', ['name' => $employeeName]) }}

{{ __('Your employee account has been created.') }}

{{ __('Login email') }}: {{ $loginEmail }}

{{ __('Activate your account') }}: {{ $activationUrl }}

{{ __('Activation link expires on :date.', ['date' => $expiresAtFormatted]) }}

{{ __('Need help? Contact support at :email.', ['email' => $supportEmail]) }}
