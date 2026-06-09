<x-admin-layout :title="__('Posting rules')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Posting rules')]]">
    @php
        $bootstrap = [
            'routes' => [
                'show' => route('admin.accounting.posting.rules.show', ['rule' => '__ID__']),
                'index' => route('admin.accounting.posting.rules.index'),
            ],
            'canAudit' => $canAudit,
            'activeFilters' => $activeFilters,
        ];
    @endphp

    <div
        class="posting-rules-workspace min-w-0"
        x-data="postingRulesWorkspace(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <x-admin.page-header
            :title="__('Posting rules')"
            :description="__('Complete visibility into how business events become journal entries. Preview only — no posting behavior changes.')"
        />

        {{-- Section 1: Summary cards --}}
        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
            <x-admin.stat-card :label="__('Total rules')" :value="$summary['total']" />
            <x-admin.stat-card :label="__('Active rules')" :value="$summary['active']" />
            <x-admin.stat-card :label="__('Auto post rules')" :value="$summary['auto_post']" />
            <x-admin.stat-card :label="__('Manual rules')" :value="$summary['manual']" />
            <x-admin.stat-card :label="__('Disabled rules')" :value="$summary['disabled']" />
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card {{ $summary['validation_errors'] > 0 ? 'ring-1 ring-red-200' : '' }}">
                <p class="text-card-title text-erp-primary">{{ __('Validation errors') }}</p>
                <p class="mt-1.5 text-card-value tabular-nums {{ $summary['validation_errors'] > 0 ? 'text-red-700' : 'text-erp-primary' }}">
                    {{ $summary['validation_errors'] }}
                </p>
            </div>
        </div>

        {{-- Section 2: Module summary panel --}}
        <div class="erp-card mb-4">
            <h2 class="erp-card-title">{{ __('Rules by module') }}</h2>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                @foreach ($moduleSummary as $module)
                    @php
                        $isActive = ($activeFilters['module'] ?? null) === $module['module'];
                        $linkFilters = $activeFilters;
                        if ($isActive) {
                            unset($linkFilters['module']);
                        } else {
                            $linkFilters['module'] = $module['module'];
                        }
                    @endphp
                    <a
                        href="{{ route('admin.accounting.posting.rules.index', $linkFilters) }}"
                        data-turbo-frame="erp-main"
                        class="posting-module-card rounded-lg border px-3 py-2.5 transition-colors {{ $isActive ? 'border-erp-accent bg-erp-accent/5 ring-1 ring-erp-accent/30' : 'border-erp-border bg-white hover:border-erp-accent/40' }}"
                    >
                        <p class="text-sm font-semibold text-erp-primary">{{ $module['label'] }}</p>
                        <dl class="mt-2 grid grid-cols-3 gap-1 text-[10px]">
                            <div>
                                <dt class="text-slate-500">{{ __('Active') }}</dt>
                                <dd class="font-semibold tabular-nums text-emerald-700">{{ $module['active'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Off') }}</dt>
                                <dd class="font-semibold tabular-nums text-slate-600">{{ $module['disabled'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Errors') }}</dt>
                                <dd class="font-semibold tabular-nums {{ $module['validation_errors'] > 0 ? 'text-red-700' : 'text-slate-600' }}">{{ $module['validation_errors'] }}</dd>
                            </div>
                        </dl>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Section 9: Filters --}}
        <form method="GET" action="{{ route('admin.accounting.posting.rules.index') }}" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="erp-card mb-4" data-turbo-frame="erp-main">
            <div class="flex flex-wrap items-center gap-2 p-4">
                <input id="filter-q" type="search" name="q" value="{{ $activeFilters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Event, name, template…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
                <select id="filter-module" name="module" class="erp-toolbar-select" aria-label="{{ __('Module') }}">
                    <option value="">{{ __('All modules') }}</option>
                    @foreach ($filterOptions['modules'] as $option)
                        <option value="{{ $option['value'] }}" @selected(($activeFilters['module'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <select id="filter-status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($filterOptions['statuses'] as $option)
                        <option value="{{ $option['value'] }}" @selected(($activeFilters['status'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <select id="filter-auto-post" name="auto_post" class="erp-toolbar-select" aria-label="{{ __('Auto post') }}">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($filterOptions['auto_post'] as $option)
                        <option value="{{ $option['value'] }}" @selected(($activeFilters['auto_post'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <select id="filter-validation" name="validation_status" class="erp-toolbar-select" aria-label="{{ __('Validation status') }}">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($filterOptions['validation_statuses'] as $option)
                        <option value="{{ $option['value'] }}" @selected(($activeFilters['validation_status'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <select id="filter-rule-type" name="rule_type" class="erp-toolbar-select" aria-label="{{ __('Rule type') }}">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($filterOptions['rule_types'] as $option)
                        <option value="{{ $option['value'] }}" @selected(($activeFilters['rule_type'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <input id="filter-created-from" type="date" name="created_from" value="{{ $activeFilters['created_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Created from') }}">
                <input id="filter-created-to" type="date" name="created_to" value="{{ $activeFilters['created_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Created to') }}">
                <input id="filter-updated-from" type="date" name="updated_from" value="{{ $activeFilters['updated_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Updated from') }}">
                <input id="filter-updated-to" type="date" name="updated_to" value="{{ $activeFilters['updated_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Updated to') }}">
                <a href="{{ route('admin.accounting.posting.rules.index') }}" data-turbo-frame="erp-main" class="erp-btn-ghost py-1 text-xs text-slate-500">{{ __('Reset') }}</a>
            </div>
            @if ($activeFilters !== [])
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($activeFilters as $key => $value)
                        <span class="erp-filter-pill erp-filter-pill--active text-xs">{{ str($key)->replace('_', ' ')->title() }}: {{ $value }}</span>
                    @endforeach
                </div>
            @endif
        </form>

        {{-- Grid --}}
        <x-admin.data-table :searchable="false" :filterable="false" :export-filename="'posting-rules'" class="erp-table--grid">
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Event / Rule') }}</th>
                    <th scope="col">{{ __('Module') }}</th>
                    <th scope="col">{{ __('Template') }}</th>
                    <th scope="col">{{ __('Auto post') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Validation status') }}</th>
                    <th scope="col" class="w-12"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($rules as $rule)
                    @php
                        $validation = $validations[$rule->id];
                    @endphp
                    <tr
                        class="cursor-pointer hover:bg-erp-page/80"
                        @click="openDrawer({{ $rule->id }})"
                    >
                        <td>
                            <span class="font-mono text-xs text-slate-600">{{ $rule->event_code }}</span>
                            <div class="text-sm font-medium text-erp-primary">{{ $rule->name }}</div>
                            @if ($rule->is_system)
                                <span class="erp-badge mt-0.5">{{ __('System') }}</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $rule->module->label() }}</td>
                        <td class="text-sm">
                            @if ($rule->template)
                                <a
                                    href="{{ route('admin.accounting.posting.templates.show', $rule->template) }}"
                                    data-turbo-frame="erp-main"
                                    class="text-erp-accent"
                                    @click.stop
                                >{{ $rule->template->code }}</a>
                            @else
                                <span class="text-red-600">—</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $rule->auto_post ? __('Yes') : __('No') }}</td>
                        <td>
                            <x-admin.status-badge :variant="$rule->is_active ? 'success' : 'neutral'">
                                {{ $rule->is_active ? __('Active') : __('Inactive') }}
                            </x-admin.status-badge>
                        </td>
                        <td>
                            <x-admin.status-badge :variant="$validation->badgeVariant()">
                                {{ $validation->label() }}
                            </x-admin.status-badge>
                        </td>
                        <td @click.stop>
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action type="button" @click="openDrawer({{ $rule->id }})">
                                    {{ __('View details') }}
                                </x-admin.table-row-action>
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-admin.empty-state icon="cog" :title="__('No posting rules match your filters')" /></td></tr>
                @endforelse
            </x-slot>
        </x-admin.data-table>

        {{-- Rule detail drawer --}}
        <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40 bg-erp-primary/40" @click="closeDrawer()" aria-hidden="true"></div>
        <aside
            x-show="drawerOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            class="posting-rule-drawer"
            role="dialog"
            aria-modal="true"
            aria-labelledby="posting-rule-drawer-title"
        >
            <div class="flex items-start justify-between border-b border-erp-border px-4 py-3">
                <div class="min-w-0 pr-3">
                    <p class="font-mono text-[11px] text-erp-accent" x-text="rule?.event_code"></p>
                    <h2 id="posting-rule-drawer-title" class="truncate text-base font-semibold text-erp-primary" x-text="rule?.name"></h2>
                    <p class="mt-0.5 text-xs text-slate-500" x-text="rule?.event_label"></p>
                </div>
                <button type="button" @click="closeDrawer()" class="shrink-0 rounded-lg p-1.5 text-slate-500 hover:bg-erp-page" aria-label="{{ __('Close') }}">
                    <x-admin.icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div x-show="drawerLoading" class="p-4 text-sm text-slate-500">{{ __('Loading rule details…') }}</div>

            <div x-show="rule && !drawerLoading" class="flex flex-1 flex-col overflow-y-auto">
                {{-- Section 3: Rule metadata --}}
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title">{{ __('Rule details') }}</h3>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">{{ __('Module') }}</dt><dd x-text="rule?.module_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Template') }}</dt><dd><a :href="rule?.template?.url" data-turbo-frame="erp-main" class="text-erp-accent" x-text="rule?.template?.code" x-show="rule?.template"></a><span x-show="!rule?.template">—</span></dd></div>
                        <div><dt class="text-slate-500">{{ __('Auto post') }}</dt><dd x-text="rule?.auto_post_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd x-text="rule?.status_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Rule type') }}</dt><dd x-text="rule?.rule_type_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Priority') }}</dt><dd x-text="rule?.priority"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Created by') }}</dt><dd x-text="rule?.created_by?.name ?? '—'"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Updated by') }}</dt><dd x-text="rule?.updated_by?.name ?? '—'"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Created') }}</dt><dd x-text="formatDate(rule?.created_at)"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Updated') }}</dt><dd x-text="formatDate(rule?.updated_at)"></dd></div>
                    </dl>
                </section>

                {{-- Section 6: Validation --}}
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title">{{ __('Rule validation') }}</h3>
                    <p class="mt-1 text-sm">
                        <span class="erp-badge" :class="validationBadgeClass(rule?.validation?.status)" x-text="rule?.validation?.label"></span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Last checked') }}: <span x-text="formatDate(rule?.validation?.validated_at)"></span></span>
                    </p>
                    <ul class="mt-2 space-y-1" x-show="rule?.validation?.issues?.length">
                        <template x-for="(issue, idx) in rule?.validation?.issues ?? []" :key="idx">
                            <li class="rounded-md border border-erp-border px-2.5 py-1.5 text-xs" :class="issue.level === 'error' ? 'border-red-200 bg-red-50/50 text-red-800' : 'border-amber-200 bg-amber-50/50 text-amber-900'" x-text="issue.message"></li>
                        </template>
                    </ul>
                </section>

                {{-- Section 8: Dependency workflow --}}
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title">{{ __('Posting workflow') }}</h3>
                    <ol class="posting-workflow mt-3 space-y-0">
                        <template x-for="(step, idx) in rule?.workflow?.steps ?? []" :key="step.key">
                            <li class="posting-workflow__step">
                                <div class="posting-workflow__node">
                                    <span class="posting-workflow__label" x-text="step.label"></span>
                                    <span class="posting-workflow__value" x-text="step.value"></span>
                                    <span class="posting-workflow__code font-mono text-[10px] text-slate-400" x-text="step.code" x-show="step.code"></span>
                                </div>
                                <div class="posting-workflow__arrow" x-show="idx < (rule?.workflow?.steps?.length ?? 0) - 1" aria-hidden="true">↓</div>
                            </li>
                        </template>
                    </ol>
                </section>

                {{-- Section 4: Account mapping --}}
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title">{{ __('Account mapping') }}</h3>
                    <div class="mt-2 space-y-2">
                        <template x-for="mapping in rule?.account_mappings ?? []" :key="mapping.line_number">
                            <div class="rounded-lg border border-erp-border px-3 py-2 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium capitalize" x-text="mapping.side_label"></span>
                                    <span class="text-[10px] text-slate-500" x-text="'#' + mapping.line_number"></span>
                                </div>
                                <template x-if="mapping.account">
                                    <dl class="mt-2 grid gap-1 text-xs sm:grid-cols-2">
                                        <div><dt class="text-slate-500">{{ __('Code') }}</dt><dd class="font-mono" x-text="mapping.account.code"></dd></div>
                                        <div><dt class="text-slate-500">{{ __('Name') }}</dt><dd x-text="mapping.account.name"></dd></div>
                                        <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd x-text="mapping.account.type ?? '—'"></dd></div>
                                        <div><dt class="text-slate-500">{{ __('Normal balance') }}</dt><dd x-text="mapping.account.normal_balance ?? '—'"></dd></div>
                                        <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd x-text="mapping.account.status_label"></dd></div>
                                    </dl>
                                </template>
                                <p x-show="!mapping.account" class="mt-1 text-xs text-amber-700" x-text="mapping.resolution_note"></p>
                                <p class="mt-1 text-[10px] text-slate-400" x-text="mapping.resolver"></p>
                            </div>
                        </template>
                    </div>
                </section>

                {{-- Section 5: Journal preview --}}
                <section class="border-b border-erp-border p-4">
                    <h3 class="posting-drawer-section-title">{{ __('Journal preview') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Expected structure when the event fires. No journal is created.') }}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Debit lines') }}</h4>
                            <ul class="mt-1 space-y-1">
                                <template x-for="line in rule?.journal_preview?.debit_lines ?? []" :key="'d-' + line.line_number">
                                    <li class="rounded-md border border-erp-border px-2 py-1.5 text-xs">
                                        <span class="font-mono text-erp-accent" x-text="line.account_code"></span>
                                        <span class="ml-1" x-text="line.account_name"></span>
                                        <span class="mt-0.5 block text-[10px] text-slate-500" x-text="line.amount_source"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Credit lines') }}</h4>
                            <ul class="mt-1 space-y-1">
                                <template x-for="line in rule?.journal_preview?.credit_lines ?? []" :key="'c-' + line.line_number">
                                    <li class="rounded-md border border-erp-border px-2 py-1.5 text-xs">
                                        <span class="font-mono text-erp-accent" x-text="line.account_code"></span>
                                        <span class="ml-1" x-text="line.account_name"></span>
                                        <span class="mt-0.5 block text-[10px] text-slate-500" x-text="line.amount_source"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- Section 7: Audit visibility --}}
                <section class="p-4" x-show="canAudit && rule?.audit">
                    <h3 class="posting-drawer-section-title">{{ __('Audit & usage') }}</h3>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">{{ __('Last validation') }}</dt><dd x-text="formatDate(rule?.audit?.last_validation_at)"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Last usage') }}</dt><dd x-text="formatDate(rule?.audit?.last_usage_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Last journal generated') }}</dt><dd x-text="formatDate(rule?.audit?.last_journal_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Last posted') }}</dt><dd x-text="formatDate(rule?.audit?.last_posted_at) ?? '—'"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Total journals') }}</dt><dd class="tabular-nums" x-text="rule?.audit?.total_journals ?? 0"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Posted journals') }}</dt><dd class="tabular-nums" x-text="rule?.audit?.posted_journals ?? 0"></dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Total amount posted') }}</dt><dd class="font-mono font-semibold tabular-nums" x-text="rule?.audit?.total_amount_posted ?? '0.00'"></dd></div>
                    </dl>
                </section>
            </div>
        </aside>
    </div>
</x-admin-layout>
