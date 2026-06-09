<x-admin-layout
    :title="__('Access Audit')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Security & Access'), 'url' => route('admin.workspaces.administration.section', ['section' => 'security-access'])],
        ['label' => __('Access Audit')],
    ]"
>
    @php
        $bootstrap = [
            'showRoute' => route('admin.security.audit.show', ['securityAuditEvent' => '__ID__']),
            'canExport' => $canExport,
            'exportRoute' => route('admin.security.audit.export'),
            'activeFilters' => array_filter($filters),
        ];
    @endphp

    <div
        class="access-audit-workspace min-w-0"
        x-data="accessAuditWorkspace(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <x-admin.page-header
            :title="__('Access Audit')"
            :description="__('Security and compliance audit trail — who did what, when, where, and from which device.')"
        />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <x-admin.kpi-widget :label="__('Audit Events Today')" :value="$metrics['events_today']" icon="document-text" />
            <x-admin.kpi-widget :label="__('Critical Events')" :value="$metrics['critical_events']" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Failed Logins')" :value="$metrics['failed_logins']" icon="shield-check" />
            <x-admin.kpi-widget :label="__('Permission Changes')" :value="$metrics['permission_changes']" icon="key" />
            <x-admin.kpi-widget :label="__('Role Changes')" :value="$metrics['role_changes']" icon="users" />
            <x-admin.kpi-widget :label="__('High Risk Actions')" :value="$metrics['high_risk_actions']" icon="flag" />
        </div>

        <x-admin.card :padding="false" class="mb-4">
            <x-admin.index-toolbar
                :action="route('admin.security.audit.index')"
                :reset-url="route('admin.security.audit.index')"
                compact
                class="erp-index-toolbar-form--compact erp-index-toolbar-form--dense"
            >
                @if ($canExport)
                    <x-slot name="export">
                        <x-admin.export-dropdown
                            export-route="admin.security.audit.export"
                            :can-export="true"
                        />
                    </x-slot>
                @endif

                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="erp-toolbar-input" aria-label="{{ __('Date from') }}">
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="erp-toolbar-input" aria-label="{{ __('Date to') }}">
                <select name="user_id" class="erp-toolbar-select" aria-label="{{ __('User') }}">
                    <option value="">{{ __('All users') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((int) $filters['user_id'] === $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="role" class="erp-toolbar-select" aria-label="{{ __('Role') }}">
                    <option value="">{{ __('All roles') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                <select name="module" class="erp-toolbar-select" aria-label="{{ __('Module') }}">
                    @foreach ($modules as $value => $label)
                        <option value="{{ $value }}" @selected($filters['module'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="entity" class="erp-toolbar-select" aria-label="{{ __('Entity') }}">
                    @foreach ($entities as $value => $label)
                        <option value="{{ $value }}" @selected($filters['entity'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="all">{{ __('All branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($filters['branch_id'] == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="risk_level" class="erp-toolbar-select" aria-label="{{ __('Risk level') }}">
                    <option value="all">{{ __('All levels') }}</option>
                    @foreach (\App\Enums\SecurityAuditRiskLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected($filters['risk_level'] === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    class="erp-toolbar-input"
                    placeholder="{{ __('Search audit events…') }}"
                    aria-label="{{ __('Search') }}"
                >
            </x-admin.index-toolbar>
        </x-admin.card>

        <x-admin.data-table :searchable="false" :exportable="false" export-filename="access-audit">
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Timestamp') }}</th>
                    <th scope="col">{{ __('User') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Module') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Entity') }}</th>
                    <th scope="col">{{ __('Action') }}</th>
                    <th scope="col" class="hidden xl:table-cell">{{ __('Description') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('IP Address') }}</th>
                    <th scope="col" class="hidden xl:table-cell">{{ __('Device') }}</th>
                    <th scope="col" class="hidden 2xl:table-cell">{{ __('Browser') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Company') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Branch') }}</th>
                    <th scope="col">{{ __('Risk') }}</th>
                    <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($events as $event)
                    @php
                        $searchBlob = strtolower(implode(' ', array_filter([
                            $event->action,
                            $event->description,
                            $event->module,
                            $event->entity,
                            $event->user?->name,
                            $event->user?->email,
                            $event->ip_address,
                            $event->device,
                            $event->browser,
                            $event->company?->name,
                            $event->branch?->name,
                            $event->risk_level->value,
                        ])));
                    @endphp
                    <tr x-show="rowVisible(@js($searchBlob), 'all')">
                        <td class="whitespace-nowrap text-slate-500">{{ $event->occurred_at?->format('M j, Y g:i A') }}</td>
                        <td>
                            <div class="font-medium text-erp-primary">{{ $event->user?->name ?? '—' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $event->user?->email }}</div>
                        </td>
                        <td class="hidden md:table-cell">{{ \Illuminate\Support\Str::headline($event->module) }}</td>
                        <td class="hidden lg:table-cell">{{ $event->entity ? \Illuminate\Support\Str::headline($event->entity) : '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::headline($event->action) }}</td>
                        <td class="hidden xl:table-cell max-w-xs truncate">{{ $event->description }}</td>
                        <td class="hidden lg:table-cell font-mono text-xs">{{ $event->ip_address ?? '—' }}</td>
                        <td class="hidden xl:table-cell">{{ $event->device ?? '—' }}</td>
                        <td class="hidden 2xl:table-cell">{{ $event->browser ?? '—' }}</td>
                        <td class="hidden md:table-cell">{{ $event->company?->name ?? '—' }}</td>
                        <td class="hidden lg:table-cell">{{ $event->branch?->name ?? '—' }}</td>
                        <td>
                            <x-admin.status-badge :variant="$event->risk_level->badgeVariant()">
                                {{ $event->risk_level->label() }}
                            </x-admin.status-badge>
                        </td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                <button
                                    type="button"
                                    class="erp-table-row-action"
                                    @click="openDrawer({{ $event->id }})"
                                >{{ __('Details') }}</button>
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">
                            <x-admin.empty-state icon="document-text" :title="__('No audit events')" :description="__('Security actions will appear here as they occur.')" />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
            <x-slot name="footer">
                <x-admin.table-pagination :paginator="$events" />
            </x-slot>
        </x-admin.data-table>

        <div
            x-show="drawerOpen"
            x-cloak
            class="fixed inset-0 z-50 flex justify-end"
            aria-modal="true"
            role="dialog"
        >
            <div class="absolute inset-0 bg-slate-900/40" @click="closeDrawer()"></div>
            <div class="relative flex h-full w-full max-w-lg flex-col bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-erp-border px-5 py-4">
                    <h2 class="text-lg font-semibold text-erp-primary">{{ __('Audit details') }}</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="closeDrawer()">
                        <x-admin.icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <template x-if="loading">
                        <p class="text-sm text-slate-500">{{ __('Loading…') }}</p>
                    </template>
                    <template x-if="!loading && detail">
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Timestamp') }}</p>
                                <p class="mt-1 text-erp-primary" x-text="detail.occurred_at_formatted"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('User') }}</p>
                                <p class="mt-1 text-erp-primary" x-text="detail.user?.name ?? '—'"></p>
                                <p class="text-xs text-slate-500" x-text="detail.user?.email"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Module') }}</p>
                                    <p class="mt-1" x-text="detail.module"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Risk') }}</p>
                                    <p class="mt-1" x-text="detail.risk_label"></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Action') }}</p>
                                <p class="mt-1" x-text="detail.description"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('IP Address') }}</p>
                                    <p class="mt-1 font-mono text-xs" x-text="detail.ip_address ?? '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Device') }}</p>
                                    <p class="mt-1" x-text="detail.device ?? '—'"></p>
                                </div>
                            </div>
                            <template x-if="detail.changed_fields?.length">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Changed fields') }}</p>
                                    <p class="mt-1" x-text="detail.changed_fields.join(', ')"></p>
                                </div>
                            </template>
                            <template x-if="detail.before_values">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Before values') }}</p>
                                    <pre class="mt-1 max-h-40 overflow-auto rounded-lg bg-erp-page p-3 text-xs" x-text="JSON.stringify(detail.before_values, null, 2)"></pre>
                                </div>
                            </template>
                            <template x-if="detail.after_values">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('After values') }}</p>
                                    <pre class="mt-1 max-h-40 overflow-auto rounded-lg bg-erp-page p-3 text-xs" x-text="JSON.stringify(detail.after_values, null, 2)"></pre>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
