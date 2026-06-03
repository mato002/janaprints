<x-admin-layout :title="__('Profile')" :breadcrumbs="[['label' => __('Profile')]]">
    <x-admin.page-header :title="__('Profile')" :description="__('Manage your account settings and security.')" />

    <div class="mx-auto max-w-3xl space-y-6">
        <x-admin.card>
            @include('profile.partials.update-profile-information-form')
        </x-admin.card>

        <x-admin.card>
            @include('profile.partials.update-password-form')
        </x-admin.card>

        <x-admin.card>
            @include('profile.partials.delete-user-form')
        </x-admin.card>
    </div>
</x-admin-layout>
