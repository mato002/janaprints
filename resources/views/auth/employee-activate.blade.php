<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <p class="font-medium text-gray-900">{{ __('Welcome, :name', ['name' => $employeeName]) }}</p>
        <p class="mt-2">{{ __('Activate your account and set a password for') }} <strong>{{ $loginEmail }}</strong>.</p>
        <p class="mt-2 text-xs text-gray-500">{{ __('This link expires on :date.', ['date' => $expiresAt->format('F j, Y g:i A')]) }}</p>
    </div>

    <form method="POST" action="{{ route('employee.activate.store', $token) }}">
        @csrf

        <x-password-input
            id="password"
            name="password"
            :label="__('Password')"
            required
            autofocus
            autocomplete="new-password"
        />

        <div class="mt-4">
            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                :label="__('Confirm Password')"
                required
                autocomplete="new-password"
            />
        </div>

        <x-input-error :messages="$errors->get('token')" class="mt-4" />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Activate account') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
