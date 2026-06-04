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

    <x-admin.data-table
        :search-placeholder="__('Search users…')"
        export-filename="users"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'active', 'label' => __('Active')],
            ['id' => 'inactive', 'label' => __('Inactive')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Role') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Last login') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($users as $user)
                @php
                    $roleName = $user->getRoleNames()->first();
                    $lastLogin = $lastLogins[$user->id] ?? null;
                    $search = strtolower($user->name.' '.$user->email.' '.($roleName ?? '').' '.($user->defaultBranch?->name ?? ''));
                    $chip = $user->is_active ? 'active' : 'inactive';
                @endphp
                <tr x-show="rowVisible(@js($search), @js($chip))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $user->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $user->email }}</div>
                    </td>
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
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $user)
                                <x-admin.table-row-action :href="route('admin.users.edit', $user)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @if ($roleName)
                                @can('viewAny', Spatie\Permission\Models\Role::class)
                                    @php $userRole = Spatie\Permission\Models\Role::query()->where('name', $roleName)->where('guard_name', 'web')->first(); @endphp
                                    @if ($userRole)
                                        <x-admin.table-row-action :href="route('admin.roles.show', $userRole)">{{ __('View access') }}</x-admin.table-row-action>
                                    @endif
                                @endcan
                            @endif
                            @can('resetPassword', $user)
                                <x-admin.table-row-action :href="route('admin.users.edit', $user).'#reset-password'">{{ __('Reset password') }}</x-admin.table-row-action>
                            @endcan
                            @can('toggleActive', $user)
                                <x-admin.table-row-action :action="route('admin.users.toggle-active', $user)" method="PATCH" :confirm="__('Change user status?')">
                                    {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                                </x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="users" :title="__('No users found')" :description="__('Create your first user to grant access to the ERP.')">
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
        <x-slot name="footer"><x-admin.table-pagination :paginator="$users" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
