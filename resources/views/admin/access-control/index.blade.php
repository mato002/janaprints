<x-admin-layout
    :title="__('Security')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Security')],
    ]"
>
    <x-admin.page-header
        :title="__('Security')"
        :description="__('Manage people and what they can access.')"
    />

    <div class="erp-card-grid">
        @if ($canViewUsers)
            @include('admin.settings.partials.control-center-card', [
                'title' => __('Users'),
                'description' => __('Accounts, branches, and role assignment.'),
                'icon' => 'users',
                'href' => route('admin.users.index'),
                'status' => __('People'),
                'statusVariant' => 'success',
            ])
        @endif

        @if ($canViewRoles)
            @include('admin.settings.partials.control-center-card', [
                'title' => __('Roles'),
                'description' => __('What each role can access across the business.'),
                'icon' => 'shield-check',
                'href' => route('admin.access-control.roles'),
                'status' => __('Access'),
                'statusVariant' => 'success',
            ])
        @endif
    </div>
</x-admin-layout>
