<x-admin-layout
    :title="__('User Sessions')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Security & Access'), 'url' => route('admin.workspaces.administration.section', ['section' => 'security-access'])],
        ['label' => __('User Sessions')],
    ]"
>
    <x-admin.page-header
        :title="__('User Sessions')"
        :description="__('Monitor active sign-ins, devices, and session activity across your organization.')"
    />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.kpi-widget :label="__('Active Sessions')" :value="$metrics['active_sessions']" icon="clock" />
        <x-admin.kpi-widget :label="__('Logged In Users')" :value="$metrics['logged_in_users']" icon="users" />
        <x-admin.kpi-widget :label="__('Failed Logins Today')" :value="$metrics['failed_logins_today']" icon="shield-check" />
        <x-admin.kpi-widget :label="__('Locked Accounts')" :value="$metrics['locked_accounts_today']" icon="lock-closed" />
        <x-admin.kpi-widget :label="__('Concurrent Sessions')" :value="$metrics['concurrent_sessions']" icon="switch-horizontal" />
    </div>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.security.sessions.index')" :reset-url="route('admin.security.sessions.index')">
            <input type="search" name="search" value="{{ $search }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search sessions…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <x-admin.status-pills
                :options="[['value' => 'all', 'label' => __('All')], ['value' => 'active', 'label' => __('Active')], ['value' => 'expired', 'label' => __('Expired')], ['value' => 'logged_out', 'label' => __('Logged Out')], ['value' => 'revoked', 'label' => __('Revoked')]]"
                param="status"
                :current="$status"
            />
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search sessions…')"
        export-filename="user-sessions"
        export-route="admin.administration.exports"
        :export-route-params="['listing' => 'user-sessions']"
        :export-query="request()->query()"
        :format-in-path="true"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'active', 'label' => __('Active')],
            ['id' => 'expired', 'label' => __('Expired')],
            ['id' => 'logged_out', 'label' => __('Logged Out')],
            ['id' => 'revoked', 'label' => __('Revoked')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col" class="hidden xl:table-cell">{{ __('Session ID') }}</th>
                <th scope="col">{{ __('User') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Role') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Company') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Branch') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('IP Address') }}</th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Device') }}</th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Browser') }}</th>
                <th scope="col" class="hidden 2xl:table-cell">{{ __('Platform') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Login Time') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Last Activity') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($sessions as $session)
                @php
                    $searchBlob = strtolower(implode(' ', array_filter([
                        (string) $session->id,
                        $session->user?->name,
                        $session->user?->email,
                        $session->role_snapshot,
                        $session->company?->name,
                        $session->branch?->name,
                        $session->ip_address,
                        $session->device,
                        $session->browser,
                        $session->platform,
                        $session->status->value,
                    ])));
                    $chip = $session->status->value;
                @endphp
                <tr x-show="rowVisible(@js($searchBlob), @js($chip))">
                    <td class="hidden xl:table-cell font-mono text-xs text-slate-500">#{{ $session->id }}</td>
                    <td>
                        <div class="font-medium text-erp-primary">{{ $session->user?->name ?? '—' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $session->user?->email }}</div>
                    </td>
                    <td class="hidden lg:table-cell">{{ $session->role_snapshot ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $session->company?->name ?? '—' }}</td>
                    <td class="hidden lg:table-cell">{{ $session->branch?->name ?? '—' }}</td>
                    <td class="hidden md:table-cell font-mono text-xs">{{ $session->ip_address ?? '—' }}</td>
                    <td class="hidden xl:table-cell">{{ $session->device ?? '—' }}</td>
                    <td class="hidden xl:table-cell">{{ $session->browser ?? '—' }}</td>
                    <td class="hidden 2xl:table-cell">{{ $session->platform ?? '—' }}</td>
                    <td class="hidden lg:table-cell text-slate-500">{{ $session->login_at?->format('M j, Y g:i A') }}</td>
                    <td class="hidden md:table-cell text-slate-500">{{ $session->last_activity_at?->diffForHumans() }}</td>
                    <td>
                        <x-admin.status-badge :variant="$session->status->badgeVariant()">
                            {{ $session->status->label() }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('view', $session)
                                <x-admin.table-row-action :href="route('admin.security.sessions.show', $session)">{{ __('Details') }}</x-admin.table-row-action>
                            @endcan
                            @can('terminate', $session)
                                @if ($session->status === \App\Enums\UserSessionStatus::Active)
                                    <x-admin.table-row-action
                                        :action="route('admin.security.sessions.terminate', $session)"
                                        method="POST"
                                        :confirm="__('Terminate this session?')"
                                    >{{ __('Terminate') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                            @can('forceLogout', $session->user)
                                @if ($session->status === \App\Enums\UserSessionStatus::Active)
                                    <x-admin.table-row-action
                                        :action="route('admin.security.sessions.force-logout', $session->user)"
                                        method="POST"
                                        :confirm="__('Force logout this user from all sessions?')"
                                    >{{ __('Force logout') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13">
                        <x-admin.empty-state icon="clock" :title="__('No sessions found')" :description="__('Active and recent sessions will appear here.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$sessions" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
