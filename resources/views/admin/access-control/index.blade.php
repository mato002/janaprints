<x-admin-layout
    :title="__('Access Control')"
    :breadcrumbs="[['label' => __('Administration')], ['label' => __('Access Control')]]"
>
    <x-admin.page-header
        :title="__('Access Control')"
        :description="__('Manage users, security groups, and access rights from one workspace.')"
    />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @if ($canViewUsers)
            @include('admin.settings.partials.control-center-card', [
                'title' => __('Users'),
                'description' => __('Manage user accounts, branches, and role assignment.'),
                'icon' => 'users',
                'href' => route('admin.users.index'),
                'status' => __('Accounts'),
                'statusVariant' => 'success',
            ])
        @endif

        @if ($canViewRoles)
            @include('admin.settings.partials.control-center-card', [
                'title' => __('Roles'),
                'description' => __('Security groups that bundle access rights for job functions.'),
                'icon' => 'key',
                'href' => route('admin.access-control.roles'),
                'status' => __('Groups'),
                'statusVariant' => 'success',
            ])

            @include('admin.settings.partials.control-center-card', [
                'title' => __('Permission Matrix'),
                'description' => __('Review what each role can view, create, edit, delete, and approve.'),
                'icon' => 'lock-closed',
                'href' => route('admin.access-control.matrix'),
                'status' => __('Matrix'),
                'statusVariant' => 'success',
            ])
        @endif
    </div>
</x-admin-layout>
