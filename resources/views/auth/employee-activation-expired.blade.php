<x-guest-layout>
    <div class="mb-4">
        <h1 class="text-lg font-semibold text-gray-900">{{ __('Activation link unavailable') }}</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('This activation link is invalid or has expired. Please contact HR or support for a new invitation.') }}
        </p>
    </div>

    <div class="flex items-center justify-end">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
            {{ __('Back to sign in') }}
        </a>
    </div>
</x-guest-layout>
