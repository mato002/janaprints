<x-admin-layout
    :title="$role->name"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Security'), 'url' => route('admin.workspaces.administration.section', ['section' => 'security-access'])],
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => $role->name],
    ]"
>
    @php
        $tab = request()->query('tab', 'overview');
        if (! in_array($tab, ['overview', 'modules', 'users', 'audit'], true)) {
            $tab = 'overview';
        }
        $initialModule = request()->query('module');
        if (is_string($initialModule) && $initialModule !== '' && $tab === 'overview') {
            $tab = 'modules';
        }
    @endphp

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <a
                href="{{ route('admin.access-control.roles') }}"
                data-turbo-action="advance"
                class="mb-1 inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-erp-accent"
            >
                <x-admin.icon name="chevron-left" class="h-3.5 w-3.5" />
                {{ __('All roles') }}
            </a>
            <h1 class="text-xl font-semibold text-erp-primary">{{ $role->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ trans_choice(':count user|:count users', $role->users_count, ['count' => $role->users_count]) }}
                <span class="mx-1.5 text-slate-300">·</span>
                {{ number_format($roleSummary['modules_enabled']) }} {{ __('modules') }}
                <span class="mx-1.5 text-slate-300">·</span>
                {{ number_format($roleSummary['permissions_enabled']) }} {{ __('permissions') }}
            </p>
        </div>
        @can('update', $role)
            <a href="{{ route('admin.roles.edit', $role) }}" class="erp-btn-secondary !px-3 !py-1.5 text-xs" data-turbo-action="advance">{{ __('Rename role') }}</a>
        @endcan
    </div>

    <nav class="mb-4 flex flex-wrap gap-1 border-b border-erp-border" aria-label="{{ __('Role sections') }}">
        @foreach ([
            'overview' => __('Overview'),
            'modules' => __('Module access'),
            'users' => __('Users'),
            'audit' => __('Audit'),
        ] as $key => $label)
            <a
                href="{{ route('admin.roles.show', ['role' => $role, 'tab' => $key]) }}"
                data-turbo-action="advance"
                class="px-3 py-2 text-sm font-medium transition-colors {{ $tab === $key ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-500 hover:text-erp-primary' }}"
            >{{ $label }}</a>
        @endforeach
    </nav>

    @if ($tab === 'overview')
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)]">
            <x-admin.card :padding="false">
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-erp-primary">{{ __('Access summary') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Click a module to open its permission matrix.') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-table erp-table--grid w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Module') }}</th>
                                <th class="w-28">{{ __('Access') }}</th>
                                <th class="w-28">{{ __('Permissions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($access['modules'] as $module)
                                <tr class="cursor-pointer hover:bg-slate-50/80">
                                    <td class="py-2.5">
                                        <a
                                            href="{{ route('admin.roles.show', ['role' => $role, 'tab' => 'modules', 'module' => $module['key']]) }}"
                                            class="font-medium text-erp-primary hover:text-erp-accent"
                                            data-turbo-action="advance"
                                        >{{ $module['label'] }}</a>
                                    </td>
                                    <td class="py-2.5">
                                        @if ($module['status'] === 'full')
                                            <span class="text-xs font-medium text-emerald-700">{{ __('Full') }}</span>
                                        @elseif ($module['status'] === 'partial')
                                            <span class="text-xs font-medium text-amber-700">{{ __('Partial') }}</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 tabular-nums text-slate-600">
                                        @if ($module['total_count'] > 0)
                                            {{ $module['enabled_count'] }} / {{ $module['total_count'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>

            <x-admin.card>
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Assigned users') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ trans_choice(':count user|:count users', $role->users_count, ['count' => $role->users_count]) }}</p>
                <div class="mt-4">
                    <a href="{{ route('admin.roles.show', ['role' => $role, 'tab' => 'users']) }}" class="erp-btn-secondary !px-3 !py-1.5 text-sm" data-turbo-action="advance">{{ __('View users') }}</a>
                </div>
            </x-admin.card>
        </div>
    @elseif ($tab === 'modules')
        @if ($canAssignPermissions)
            <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="space-y-3">
                @csrf
                @method('PUT')

                @include('admin.roles.partials.module-access', [
                    'access' => $access,
                    'editable' => true,
                    'initialModule' => is_string($initialModule) ? $initialModule : null,
                    'roleName' => $role->name,
                ])

                <div class="sticky bottom-3 z-10 flex justify-end gap-2 rounded-lg border border-erp-border bg-erp-card/95 px-4 py-2 shadow-lg backdrop-blur-sm">
                    <a href="{{ route('admin.roles.show', ['role' => $role, 'tab' => 'modules']) }}" class="erp-btn-secondary !px-3 !py-1.5 text-sm" data-turbo-action="advance">{{ __('Cancel') }}</a>
                    <x-primary-button class="!px-4 !py-1.5 text-sm">{{ __('Save changes') }}</x-primary-button>
                </div>
            </form>
        @else
            @include('admin.roles.partials.module-access', [
                'access' => $access,
                'editable' => false,
                'initialModule' => is_string($initialModule) ? $initialModule : null,
                'roleName' => $role->name,
            ])
        @endif
    @elseif ($tab === 'users')
        <x-admin.card>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Assigned users') }}</h2>

            @if ($assignedUsers->isEmpty())
                <p class="mt-2 text-sm text-slate-500">{{ __('No users assigned to this role yet.') }}</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="erp-table text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignedUsers as $user)
                                <tr>
                                    <td class="font-medium text-erp-primary">
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user) }}" class="hover:text-erp-accent" data-turbo-action="advance">{{ $user->name }}</a>
                                        @else
                                            {{ $user->name }}
                                        @endcan
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->defaultBranch?->name ?? '—' }}</td>
                                    <td>
                                        <x-admin.status-badge :variant="$user->is_active ? 'success' : 'danger'">
                                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                                        </x-admin.status-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    @else
        <x-admin.card>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Audit') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('Review authentication and authorization events for people in this role.') }}</p>
            <div class="mt-4">
                @can('security.audit.view')
                    <a href="{{ route('admin.security.audit.index') }}" class="erp-btn-secondary !px-3 !py-1.5 text-sm" data-turbo-action="advance">{{ __('Open Access Audit') }}</a>
                @else
                    <p class="text-sm text-slate-400">{{ __('You do not have permission to view access audit.') }}</p>
                @endcan
            </div>
        </x-admin.card>
    @endif
</x-admin-layout>
