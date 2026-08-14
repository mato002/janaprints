<x-admin-layout
    :title="__('Roles')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Security'), 'url' => route('admin.workspaces.administration.section', ['section' => 'security-access'])],
        ['label' => __('Roles')],
    ]"
>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-erp-primary">{{ __('Roles') }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('Who can access what across the business.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('create', Spatie\Permission\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" data-turbo-frame="erp-main" data-turbo-action="advance" class="erp-btn-primary !px-3 !py-1.5 text-sm">{{ __('New role') }}</a>
            @endcan
            @can('viewAny', Spatie\Permission\Models\Role::class)
                <x-admin.export-dropdown
                    export-route="admin.roles.export"
                    :export-query="request()->query()"
                    :format-in-path="true"
                />
            @endcan
        </div>
    </div>

    <x-admin.card :padding="false" x-data="roleAccessList()">
        <div class="border-b border-erp-border px-4 py-2.5">
            <div class="relative max-w-md">
                <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    x-model="query"
                    class="erp-input w-full py-1.5 pl-8 text-sm"
                    placeholder="{{ __('Search roles…') }}"
                    aria-label="{{ __('Search roles') }}"
                >
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th class="w-24">{{ __('Users') }}</th>
                        <th>{{ __('Access') }}</th>
                        <th class="erp-table-actions-col w-16 text-right"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-erp-border bg-white">
                    @forelse ($profiles as $profile)
                        <tr
                            x-show="matches(@js($profile['name'].' '.($profile['access_summary'] ?? '')))"
                            class="transition-colors hover:bg-slate-50/80 {{ ! empty($profile['is_deactivated']) ? 'opacity-60' : '' }}"
                            data-href="{{ $profile['show_url'] }}"
                            data-turbo-frame="erp-main"
                            role="link"
                            tabindex="0"
                            @click="if (! $event.target.closest('a, button, [data-erp-row-actions], form')) { const url = $el.dataset.href; if (window.Turbo) { Turbo.visit(url, { frame: 'erp-main', action: 'advance' }); } else { window.location = url; } }"
                            @keydown.enter.prevent="if (! $event.target.closest('a, button, [data-erp-row-actions], form')) { $el.click(); }"
                        >
                            <td class="py-3">
                                <a
                                    href="{{ $profile['show_url'] }}"
                                    class="font-medium text-erp-primary hover:text-erp-accent"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >{{ $profile['name'] }}</a>
                                @if (! empty($profile['is_deactivated']))
                                    <span class="ml-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Deactivated') }}</span>
                                @endif
                            </td>
                            <td class="py-3 tabular-nums text-slate-700">{{ number_format($profile['users_count']) }}</td>
                            <td class="py-3 text-slate-600">{{ $profile['access_summary'] ?? $profile['modules_display'] }}</td>
                            <td class="erp-table-actions-col py-3 text-right" @click.stop>
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="$profile['show_url']">
                                        {{ __('Open') }}
                                    </x-admin.table-row-action>

                                    <x-admin.table-row-action :href="route('admin.roles.show', ['role' => $profile['id'], 'tab' => 'modules'])">
                                        {{ __('Manage access') }}
                                    </x-admin.table-row-action>

                                    @if (! empty($profile['edit_url']))
                                        <x-admin.table-row-action :href="$profile['edit_url']">
                                            {{ __('Rename') }}
                                        </x-admin.table-row-action>
                                    @endif

                                    @if (! empty($profile['can_clone']))
                                        <x-admin.table-row-action
                                            :action="route('admin.roles.duplicate', $profile['id'])"
                                            method="POST"
                                        >
                                            {{ __('Duplicate') }}
                                        </x-admin.table-row-action>
                                    @endif

                                    @if (! empty($profile['can_deactivate']))
                                        @if (! empty($profile['is_deactivated']))
                                            <x-admin.table-row-action
                                                :action="route('admin.roles.reactivate', $profile['id'])"
                                                method="PATCH"
                                            >
                                                {{ __('Reactivate') }}
                                            </x-admin.table-row-action>
                                        @elseif (! empty($profile['deactivate_blocked']))
                                            <button
                                                type="button"
                                                class="flex w-full cursor-not-allowed items-center gap-2 px-3 py-2 text-left text-sm text-slate-400"
                                                title="{{ __('Remove all users before deactivating this role.') }}"
                                                disabled
                                            >{{ __('Deactivate') }}</button>
                                        @else
                                            <x-admin.table-row-action
                                                :action="route('admin.roles.deactivate', $profile['id'])"
                                                method="PATCH"
                                                :confirm="__('Deactivate this role? Permissions are kept for audit history.')"
                                                variant="danger"
                                            >
                                                {{ __('Deactivate') }}
                                            </x-admin.table-row-action>
                                        @endif

                                        @if ($profile['users_count'] === 0)
                                            <x-admin.table-row-action
                                                :action="route('admin.roles.destroy', $profile['id'])"
                                                method="DELETE"
                                                :confirm="__('Permanently delete this role? This cannot be undone.')"
                                                variant="danger"
                                            >
                                                {{ __('Delete') }}
                                            </x-admin.table-row-action>
                                        @endif
                                    @endif
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No roles yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="border-t border-erp-border px-4 py-3">
                {{ $roles->links() }}
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
