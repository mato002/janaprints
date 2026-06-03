<x-admin-layout
    :title="$role->name"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => $role->name],
    ]"
>
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <a
                href="{{ route('admin.access-control.roles') }}"
                data-turbo-action="advance"
                class="mb-1 inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-erp-accent"
            >
                <x-admin.icon name="chevron-left" class="h-3.5 w-3.5" />
                {{ __('All roles') }}
            </a>
            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                <h1 class="text-lg font-semibold text-erp-primary">{{ $role->name }}</h1>
                <p class="text-xs text-slate-500">
                    {{ __('Users') }}: <span class="font-medium text-slate-700">{{ number_format($role->users_count) }}</span>
                    <span class="mx-1.5 text-slate-300">·</span>
                    {{ __('Modules') }}: <span class="font-medium text-slate-700">{{ number_format($roleSummary['modules_enabled']) }}</span>
                    <span class="mx-1.5 text-slate-300">·</span>
                    {{ __('Permissions') }}: <span class="font-medium text-slate-700">{{ number_format($roleSummary['permissions_enabled']) }}</span>
                    <span class="mx-1.5 text-slate-300">·</span>
                    {{ $role->updated_at?->format('M j, Y') ?? '—' }}
                </p>
            </div>
        </div>
        @can('update', $role)
            <a href="{{ route('admin.roles.edit', $role) }}" class="erp-btn-secondary !px-3 !py-1.5 text-xs" data-turbo-action="advance">{{ __('Rename role') }}</a>
        @endcan
    </div>

    @if ($canAssignPermissions)
        <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="space-y-2">
            @csrf
            @method('PUT')

            @include('admin.access-control.partials.matrix-workspace', [
                'workspace' => $workspace,
                'editable' => true,
                'storageKey' => 'erp.permissionMatrix.role.'.$role->id,
            ])

            <div class="sticky bottom-3 z-10 flex justify-end rounded-lg border border-erp-border bg-erp-card/95 px-4 py-2 shadow-lg backdrop-blur-sm">
                <x-primary-button class="!px-4 !py-1.5 text-sm">{{ __('Save access rights') }}</x-primary-button>
            </div>
        </form>
    @else
        @include('admin.access-control.partials.matrix-workspace', [
            'workspace' => $workspace,
            'editable' => false,
            'storageKey' => 'erp.permissionMatrix.role.'.$role->id,
        ])
    @endif

    <x-admin.card class="mt-6">
        <h2 class="text-sm font-semibold text-erp-primary">{{ __('Assigned users') }}</h2>

        @if ($assignedUsers->isEmpty())
            <p class="mt-2 text-xs text-slate-500">{{ __('No users assigned to this role yet.') }}</p>
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
</x-admin-layout>
