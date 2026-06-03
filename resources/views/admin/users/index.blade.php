<x-admin-layout
    :title="__('Users')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Users')],
    ]"
>
    <x-admin.page-header :title="__('Users')" :description="__('Manage platform users, branches, and security group assignment.')">
        <x-slot name="actions">
            @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="erp-btn-primary">{{ __('Create user') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :searchable="true">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('User') }}</th>
                <th scope="col">{{ __('Email') }}</th>
                <th scope="col">{{ __('Role') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Last login') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($users as $user)
                @php
                    $roleName = $user->getRoleNames()->first();
                    $lastLogin = $lastLogins[$user->id] ?? null;
                @endphp
                <tr x-show="matches(@js($user->name.' '.$user->email.' '.($roleName ?? '').' '.($user->defaultBranch?->name ?? '')))">
                    <td class="font-medium text-erp-primary">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $roleName ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $user->defaultBranch?->name ?? '—' }}</td>
                    <td>
                        <x-admin.status-badge :variant="$user->is_active ? 'success' : 'danger'">
                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="hidden lg:table-cell text-slate-500">
                        @if ($lastLogin)
                            {{ \Illuminate\Support\Carbon::parse($lastLogin)->format('M j, Y g:i A') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex flex-wrap justify-end gap-x-3 gap-y-1 text-sm">
                            @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-erp-accent hover:underline" data-turbo-action="advance">{{ __('Edit') }}</a>
                            @endcan
                            @if ($roleName)
                                @can('viewAny', Spatie\Permission\Models\Role::class)
                                    @php $userRole = Spatie\Permission\Models\Role::query()->where('name', $roleName)->where('guard_name', 'web')->first(); @endphp
                                    @if ($userRole)
                                        <a href="{{ route('admin.roles.show', $userRole) }}" class="font-medium text-slate-600 hover:underline" data-turbo-action="advance">{{ __('View access') }}</a>
                                    @endif
                                @endcan
                            @endif
                            @can('resetPassword', $user)
                                <a href="{{ route('admin.users.edit', $user) }}#reset-password" class="font-medium text-slate-600 hover:underline" data-turbo-action="advance">{{ __('Reset password') }}</a>
                            @endcan
                            @can('toggleActive', $user)
                                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="font-medium text-amber-700 hover:underline">
                                        {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state
                            icon="users"
                            :title="__('No users found')"
                            :description="__('Create your first user to grant access to the ERP.')"
                        >
                            <x-slot name="action">
                                @can('create', App\Models\User::class)
                                    <a href="{{ route('admin.users.create') }}" class="erp-btn-primary">{{ __('Create user') }}</a>
                                @endcan
                            </x-slot>
                        </x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $users->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
