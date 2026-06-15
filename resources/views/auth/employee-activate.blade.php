<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <p class="font-medium text-gray-900">{{ __('Welcome, :name', ['name' => $employeeName]) }}</p>
        <p class="mt-2">{{ __('Activate your Jana Prints account and set a password for') }} <strong>{{ $corporateEmail }}</strong>.</p>
        <p class="mt-2 text-xs text-gray-500">{{ __('This link expires on :date.', ['date' => $expiresAt->format('F j, Y g:i A')]) }}</p>
    </div>

    <form method="POST" action="{{ route('employee.activate.store', $token) }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-input-error :messages="$errors->get('token')" class="mt-4" />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Activate account') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
