<x-admin-layout
    :title="__('Roles')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Roles')],
    ]"
>
    <div x-data="roleGovernanceDashboard()" x-cloak>
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-erp-primary">{{ __('Security Groups') }}</h1>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Enterprise access governance across departments and job functions.') }}</p>
            </div>
            @can('create', Spatie\Permission\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="erp-btn-primary !px-3 !py-1.5 text-sm">{{ __('Create role') }}</a>
            @endcan
        </div>

        <div class="role-governance-panel mb-3 rounded-lg border border-erp-border bg-erp-page/60 px-4 py-2.5">
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                <span><span class="text-slate-400">{{ __('Roles') }}:</span> <strong class="text-erp-primary">{{ number_format($panel['total_roles']) }}</strong></span>
                <span><span class="text-slate-400">{{ __('Active') }}:</span> <strong class="text-emerald-700">{{ number_format($panel['active']) }}</strong></span>
                <span><span class="text-slate-400">{{ __('Draft') }}:</span> <strong class="text-sky-700">{{ number_format($panel['draft']) }}</strong></span>
                <span><span class="text-slate-400">{{ __('Broken') }}:</span> <strong class="text-red-700">{{ number_format($panel['broken']) }}</strong></span>
                <span><span class="text-slate-400">{{ __('Unused') }}:</span> <strong class="text-slate-700">{{ number_format($panel['unused']) }}</strong></span>
                @if ($panel['deactivated'] > 0)
                    <span><span class="text-slate-400">{{ __('Deactivated') }}:</span> <strong class="text-slate-500">{{ number_format($panel['deactivated']) }}</strong></span>
                @endif
                <span><span class="text-slate-400">{{ __('Users assigned') }}:</span> <strong class="text-erp-primary">{{ number_format($panel['assigned_users']) }}</strong></span>
            </div>
        </div>

        <div class="mb-3 flex flex-wrap gap-x-5 gap-y-1 rounded-lg border border-erp-border bg-white px-4 py-2 text-[11px] text-slate-600">
            @if ($insights['most_used'])
                <span><span class="font-medium text-slate-500">{{ __('Most used') }}:</span> {{ $insights['most_used']['name'] }} ({{ $insights['most_used']['users_count'] }})</span>
            @endif
            @if ($insights['least_used'])
                <span><span class="font-medium text-slate-500">{{ __('Least used') }}:</span> {{ $insights['least_used']['name'] }} ({{ $insights['least_used']['users_count'] }})</span>
            @endif
            <span><span class="font-medium text-slate-500">{{ __('Without users') }}:</span> {{ number_format($insights['roles_without_users']) }}</span>
            <span><span class="font-medium text-slate-500">{{ __('Without permissions') }}:</span> {{ number_format($insights['roles_without_permissions']) }}</span>
            <span><span class="font-medium text-slate-500">{{ __('Broken roles') }}:</span> {{ number_format($insights['broken_roles']) }}</span>
            <span><span class="font-medium text-slate-500">{{ __('Draft roles') }}:</span> {{ number_format($insights['draft_roles']) }}</span>
        </div>

        <x-admin.card :padding="false">
            <div class="border-b border-erp-border px-4 py-2.5">
                <div class="relative max-w-md">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                    <input
                        type="search"
                        x-model="query"
                        class="erp-input w-full py-1.5 pl-8 text-sm"
                        placeholder="{{ __('Search roles, categories, modules, users…') }}"
                        aria-label="{{ __('Search roles') }}"
                    >
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Users assigned') }}</th>
                            <th>{{ __('Permissions') }}</th>
                            <th>{{ __('Modules') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-erp-border bg-white">
                        @forelse ($profiles as $profile)
                            <tr
                                x-show="matches(@js($profile['search_text']))"
                                class="cursor-pointer transition-colors hover:bg-slate-50/80 {{ $profile['is_deactivated'] ? 'bg-slate-50/80 opacity-75' : '' }}"
                                @click="openRole(@js($profile['show_url']))"
                                @mouseenter="setPreview(@js($profile))"
                                @mouseleave="clearPreview()"
                            >
                                <td class="py-2.5">
                                    <div class="relative">
                                        <span class="font-medium text-erp-primary">
                                            {{ $profile['name'] }}
                                        </span>
                                        <div
                                            x-show="previewRole && previewRole.id === {{ $profile['id'] }}"
                                            x-transition
                                            class="pointer-events-none absolute left-0 top-full z-20 mt-1 w-64 rounded-lg border border-erp-border bg-white p-3 text-xs shadow-lg"
                                        >
                                            <p class="font-semibold text-erp-primary" x-text="previewRole?.name"></p>
                                            <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Access coverage') }}</p>
                                            <ul class="mt-1.5 space-y-0.5">
                                                <template x-for="module in previewRole?.module_coverage ?? []" :key="module.key">
                                                    <li class="flex items-center justify-between gap-2 text-slate-600">
                                                        <span x-text="module.label"></span>
                                                        <span class="font-semibold" :class="module.enabled ? 'text-emerald-600' : 'text-slate-300'" x-text="module.enabled ? '✓' : '✗'"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <span class="role-category-badge role-category-badge--{{ $profile['category']['tone'] }}">
                                        {{ $profile['category']['label'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 tabular-nums" @click.stop>
                                    @if ($profile['users_count'] > 0)
                                        <button
                                            type="button"
                                            @click.stop="openDrawer(@js($profile))"
                                            class="font-medium text-erp-accent hover:underline"
                                        >
                                            {{ number_format($profile['users_count']) }}
                                        </button>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="py-2.5 tabular-nums">{{ number_format($profile['permissions_count']) }}</td>
                                <td class="max-w-[12rem] py-2.5">
                                    @if ($profile['modules_enabled'] > 0)
                                        <span class="block truncate text-slate-700" title="{{ $profile['modules_display'] }}">
                                            {{ $profile['modules_display'] }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">{{ __(':count enabled', ['count' => $profile['modules_enabled']]) }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5">
                                    <x-admin.status-badge :variant="$profile['health']['tone']">
                                        {{ $profile['health']['label'] }}
                                    </x-admin.status-badge>
                                </td>
                                <td class="py-2.5 text-right" @click.stop>
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-erp-page hover:text-erp-primary" aria-label="{{ __('Role actions') }}">
                                                <x-admin.icon name="ellipsis-vertical" class="h-4 w-4" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            <a href="{{ $profile['show_url'] }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-erp-page" data-turbo-action="advance">{{ __('Open') }}</a>
                                            @if ($profile['can_clone'])
                                                <form method="POST" action="{{ route('admin.roles.duplicate', $profile['id']) }}">
                                                    @csrf
                                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-erp-page">{{ __('Clone') }}</button>
                                                </form>
                                            @endif
                                            @if ($profile['edit_url'])
                                                <a href="{{ $profile['edit_url'] }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-erp-page" data-turbo-action="advance">{{ __('Rename') }}</a>
                                            @endif
                                            @if ($profile['can_deactivate'])
                                                @if ($profile['is_deactivated'])
                                                    <form method="POST" action="{{ route('admin.roles.reactivate', $profile['id']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-erp-page">{{ __('Reactivate') }}</button>
                                                    </form>
                                                @elseif ($profile['deactivate_blocked'])
                                                    <span class="block px-4 py-2 text-sm text-slate-400" title="{{ __('Remove users before deactivating.') }}">{{ __('Deactivate') }}</span>
                                                @else
                                                    <form method="POST" action="{{ route('admin.roles.deactivate', $profile['id']) }}" onsubmit="return confirm(@js(__('Deactivate this role? Permissions are preserved for audit history.')))">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-amber-700 hover:bg-amber-50">{{ __('Deactivate') }}</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </x-slot>
                                    </x-dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-admin.empty-state icon="key" :title="__('No roles found')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <div class="mt-4">{{ $roles->links() }}</div>

        <div
            x-show="drawerOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/30"
            @click="closeDrawer()"
            style="display: none;"
        ></div>

        <div
            x-show="drawerOpen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="role-governance-drawer"
            @keydown.escape.window="closeDrawer()"
            style="display: none;"
        >
            <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                <div>
                    <p class="text-xs text-slate-500">{{ __('Assigned users') }}</p>
                    <h2 class="text-base font-semibold text-erp-primary" x-text="drawerRole?.name"></h2>
                </div>
                <button type="button" @click="closeDrawer()" class="rounded-lg p-1.5 text-slate-500 hover:bg-erp-page" aria-label="{{ __('Close') }}">
                    <x-admin.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="drawerRole && drawerRole.users.length === 0">
                    <p class="text-sm text-slate-500">{{ __('No users assigned to this role yet.') }}</p>
                </template>
                <ul class="space-y-2" x-show="drawerRole && drawerRole.users.length > 0">
                    <template x-for="user in drawerRole?.users ?? []" :key="user.id">
                        <li class="rounded-lg border border-erp-border px-3 py-2">
                            <template x-if="user.edit_url">
                                <a :href="user.edit_url" class="block font-medium text-erp-primary hover:text-erp-accent" data-turbo-action="advance" x-text="user.name"></a>
                            </template>
                            <template x-if="! user.edit_url">
                                <span class="block font-medium text-erp-primary" x-text="user.name"></span>
                            </template>
                            <p class="text-xs text-slate-500" x-text="user.email"></p>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="border-t border-erp-border px-4 py-3">
                <a
                    :href="drawerRole?.show_url"
                    class="erp-btn-secondary w-full justify-center !py-1.5 text-sm"
                    data-turbo-action="advance"
                >
                    {{ __('Open role permissions') }}
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
