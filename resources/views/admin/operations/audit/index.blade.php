<x-admin-layout
    :title="__('Audit Logs')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('Audit Logs')],
    ]"
>
    @php
        $bootstrap = [
            'showRoute' => route('admin.operations.audit.show', ['securityAuditEvent' => '__ID__']),
            'canExport' => $canExport,
            'exportRoute' => route('admin.operations.audit.export'),
            'activeFilters' => array_filter($filters),
        ];
    @endphp

    <div
        class="audit-logs-center min-w-0"
        x-data="auditLogsWorkspace(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <x-admin.page-header
            :title="__('Audit Logs')"
            :description="__('Immutable compliance history — configuration, governance, inventory, and accounting events. Not general activity history.')"
        />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <x-admin.kpi-widget :label="__('Events Today')" :value="$metrics['events_today']" icon="document-text" />
            <x-admin.kpi-widget :label="__('Critical Events')" :value="$metrics['critical_events']" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Permission Changes')" :value="$metrics['permission_changes']" icon="key" />
            <x-admin.kpi-widget :label="__('Configuration Changes')" :value="$metrics['configuration_changes']" icon="cog" />
            <x-admin.kpi-widget :label="__('Inventory Adjustments')" :value="$metrics['inventory_adjustments']" icon="archive" />
            <x-admin.kpi-widget :label="__('Accounting Events')" :value="$metrics['accounting_events']" icon="currency-dollar" />
        </div>

        <form method="GET" action="{{ route('admin.operations.audit.index') }}" class="erp-card mb-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="erp-label">{{ __('Date from') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="erp-input w-full text-sm" />
                </div>
                <div>
                    <label class="erp-label">{{ __('Date to') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="erp-input w-full text-sm" />
                </div>
                <div>
                    <label class="erp-label">{{ __('User') }}</label>
                    <select name="user_id" class="erp-input w-full text-sm">
                        <option value="">{{ __('All users') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((int) $filters['user_id'] === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Compliance Event') }}</label>
                    <select name="category" class="erp-input w-full text-sm">
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Module') }}</label>
                    <select name="module" class="erp-input w-full text-sm">
                        @foreach ($moduleOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['module'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Risk level') }}</label>
                    <select name="risk_level" class="erp-input w-full text-sm">
                        @foreach ($riskOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['risk_level'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-2">
                    <label class="erp-label">{{ __('Search') }}</label>
                    <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-input w-full text-sm" placeholder="{{ __('Search audit logs…') }}" />
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply filters') }}</button>
                <a href="{{ route('admin.operations.audit.index') }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
                @if ($canExport)
                    <div class="ml-auto flex flex-wrap gap-2">
                        <a :href="exportUrl('csv')" class="erp-btn-secondary text-sm">{{ __('Export CSV') }}</a>
                        <a :href="exportUrl('excel')" class="erp-btn-secondary text-sm">{{ __('Export Excel') }}</a>
                        <a :href="exportUrl('pdf')" class="erp-btn-secondary text-sm">{{ __('Export PDF') }}</a>
                    </div>
                @endif
            </div>
        </form>

        <x-admin.data-table :search-placeholder="__('Search audit logs…')" export-filename="audit-logs">
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Timestamp') }}</th>
                    <th scope="col">{{ __('User') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Module') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Entity') }}</th>
                    <th scope="col">{{ __('Action') }}</th>
                    <th scope="col" class="hidden xl:table-cell">{{ __('Old Value') }}</th>
                    <th scope="col" class="hidden xl:table-cell">{{ __('New Value') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('IP') }}</th>
                    <th scope="col" class="hidden 2xl:table-cell">{{ __('Device') }}</th>
                    <th scope="col">{{ __('Risk Level') }}</th>
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
                            $auditService->formatValues($event->before_values),
                            $auditService->formatValues($event->after_values),
                        ])));
                    @endphp
                    <tr x-show="rowVisible(@js($searchBlob))">
                        <td class="text-slate-500">{{ $event->occurred_at?->format('M j, Y g:i A') }}</td>
                        <td class="font-medium">{{ $event->user?->name ?? '—' }}</td>
                        <td class="hidden md:table-cell">{{ \Illuminate\Support\Str::headline($event->module) }}</td>
                        <td class="hidden lg:table-cell">{{ $event->entity ? \Illuminate\Support\Str::headline($event->entity) : '—' }}</td>
                        <td>{{ $auditService->actionLabel($event) }}</td>
                        <td class="hidden xl:table-cell font-mono text-xs text-slate-600">{{ $auditService->formatValues($event->before_values) }}</td>
                        <td class="hidden xl:table-cell font-mono text-xs text-slate-600">{{ $auditService->formatValues($event->after_values) }}</td>
                        <td class="hidden lg:table-cell font-mono text-xs">{{ $event->ip_address ?? '—' }}</td>
                        <td class="hidden 2xl:table-cell">{{ $event->device ?? '—' }}</td>
                        <td>
                            <x-admin.status-badge :variant="$event->risk_level->badgeVariant()">
                                {{ $event->risk_level->label() }}
                            </x-admin.status-badge>
                        </td>
                        <td class="erp-table-actions-col">
                            <button
                                type="button"
                                class="erp-btn-secondary px-2 py-1 text-xs"
                                @click="openDrawer({{ $event->id }})"
                            >
                                {{ __('View') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <x-admin.empty-state
                                icon="document-text"
                                :title="__('No compliance audit events yet')"
                                :description="__('Governance, configuration, inventory, and accounting changes will appear here as immutable audit records.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
            <x-slot name="footer">
                <x-admin.table-pagination :paginator="$events" />
            </x-slot>
        </x-admin.data-table>

        <div
            x-cloak
            x-show="drawerOpen"
            class="fixed inset-0 z-40 flex justify-end bg-slate-900/40"
            @click.self="closeDrawer()"
        >
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-erp-border px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-erp-primary">{{ __('Audit Event Detail') }}</h2>
                        <p class="text-sm text-slate-500" x-text="detail?.occurred_at_formatted ?? ''"></p>
                    </div>
                    <button type="button" class="erp-btn-secondary text-sm" @click="closeDrawer()">{{ __('Close') }}</button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <template x-if="loading">
                        <p class="text-sm text-slate-500">{{ __('Loading…') }}</p>
                    </template>
                    <template x-if="!loading && detail">
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('User') }}</p>
                                <p class="mt-1 font-medium" x-text="detail.user?.name ?? '—'"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Module') }}</p>
                                    <p class="mt-1" x-text="detail.module"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Entity') }}</p>
                                    <p class="mt-1" x-text="detail.entity ?? '—'"></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Action') }}</p>
                                <p class="mt-1 font-medium" x-text="detail.action"></p>
                                <p class="mt-1 text-slate-600" x-text="detail.description"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Old Value') }}</p>
                                <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-100" x-text="JSON.stringify(detail.old_value ?? {}, null, 2)"></pre>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('New Value') }}</p>
                                <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-100" x-text="JSON.stringify(detail.new_value ?? {}, null, 2)"></pre>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('IP') }}</p>
                                    <p class="mt-1 font-mono text-xs" x-text="detail.ip_address ?? '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Device') }}</p>
                                    <p class="mt-1" x-text="detail.device ?? '—'"></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Risk Level') }}</p>
                                <p class="mt-1 font-medium" x-text="detail.risk_label"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
