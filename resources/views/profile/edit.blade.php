<x-admin-layout :title="__('Profile')" :breadcrumbs="[['label' => __('Profile')]]">
    <x-admin.page-header :title="__('Profile')" :description="__('Manage your account settings, access, and security.')">
        <x-slot name="actions">
            <a href="{{ route('profile.sessions.index') }}" class="erp-btn-secondary">{{ __('My Sessions') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status') && ! in_array(session('status'), ['profile-updated', 'avatar-removed', 'password-updated'], true))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-2 lg:items-stretch">
        <x-admin.card class="h-full">
            @include('profile.partials.update-profile-information-form')
        </x-admin.card>

        <x-admin.card class="h-full">
            @include('profile.partials.authentication-summary')
        </x-admin.card>

        <x-admin.card class="h-full">
            @include('profile.partials.roles-summary')
        </x-admin.card>

        <x-admin.card class="h-full">
            @include('profile.partials.sessions-summary')
        </x-admin.card>

        <x-admin.card class="h-full lg:col-span-2">
            @include('profile.partials.permissions-summary')
        </x-admin.card>

        <x-admin.card class="h-full">
            @include('profile.partials.update-password-form')
        </x-admin.card>

        <x-admin.card class="h-full">
            @include('profile.partials.delete-user-form')
        </x-admin.card>
    </div>
</x-admin-layout>
