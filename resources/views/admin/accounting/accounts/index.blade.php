<x-admin-layout :title="__('Chart of Accounts')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Chart of Accounts')]]">
    <div
        class="coa-explorer"
        x-data="chartOfAccountsExplorer(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <div class="coa-explorer__header">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-lg font-semibold text-erp-primary">{{ __('Chart of Accounts') }}</h1>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('Company general ledger account structure') }}</p>
                </div>
                @if ($bootstrap['permissions']['create'])
                    <a href="{{ $bootstrap['routes']['create'] }}" data-turbo-frame="erp-main" data-turbo-action="advance" class="erp-btn-primary text-sm">{{ __('New account') }}</a>
                @endif
            </div>

            <div class="relative mt-3 max-w-xl">
                <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    x-model="query"
                    @input.debounce.300ms="runSearch()"
                    class="erp-input w-full py-2 pl-9 text-sm"
                    placeholder="{{ __('Search code, name, or group…') }}"
                    aria-label="{{ __('Search accounts') }}"
                >
                <div
                    x-show="searchOpen && searchResults.length > 0"
                    x-cloak
                    class="absolute left-0 right-0 top-full z-20 mt-1 max-h-56 overflow-y-auto rounded-lg border border-erp-border bg-white shadow-lg"
                >
                    <template x-for="hit in searchResults" :key="hit.account_id">
                        <button
                            type="button"
                            @click="goToSearchResult(hit)"
                            class="flex w-full flex-col border-b border-erp-border px-3 py-2 text-left text-sm last:border-0 hover:bg-erp-page"
                        >
                            <span class="font-mono text-[11px] text-erp-accent" x-text="hit.code"></span>
                            <span class="font-medium text-erp-primary" x-text="hit.name"></span>
                            <span class="text-[10px] text-slate-500" x-text="(hit.type_name || '') + (hit.group_name ? ' · ' + hit.group_name : '')"></span>
                        </button>
                    </template>
                </div>
                <p x-show="searchOpen && query.trim() && searchResults.length === 0 && !searchLoading" x-cloak class="absolute left-0 right-0 top-full z-20 mt-1 rounded-lg border border-erp-border bg-white px-3 py-3 text-center text-xs text-slate-500 shadow-lg">
                    {{ __('No matches') }}
                </p>
            </div>
        </div>

        <div class="coa-explorer__kpi mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Total accounts') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-erp-primary" x-text="stats.total"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Active accounts') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-700" x-text="stats.active"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Locked accounts') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-amber-700" x-text="stats.locked"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Account groups') }}</p>
                <p class="mt-0.5 text-lg font-bold tabular-nums text-erp-primary" x-text="stats.groups"></p>
            </div>
        </div>

        <div class="coa-explorer__mobile-nav mb-2 flex items-center gap-2 lg:hidden" x-show="mobileStack !== 'types'" x-cloak>
            <button type="button" @click="mobileBack()" class="inline-flex items-center gap-1 rounded-md border border-erp-border px-2 py-1 text-xs text-erp-primary hover:bg-erp-page">
                <x-admin.icon name="chevron-left" class="h-4 w-4" />
                {{ __('Back') }}
            </button>
            <span class="truncate text-xs text-slate-500" x-text="mobileTitle()"></span>
        </div>

        <div class="coa-explorer__workspace">
            <aside
                class="coa-explorer__panel coa-explorer__panel--types"
                :class="{ 'coa-explorer__panel--hidden-mobile': mobileStack !== 'types' }"
            >
                <h2 class="coa-explorer__panel-title">{{ __('Account types') }}</h2>
                <ul class="space-y-0.5" role="listbox">
                    <template x-for="type in types" :key="type.id">
                        <li>
                            <button
                                type="button"
                                @click="selectType(type.id)"
                                class="coa-type-btn w-full rounded-md px-2.5 py-2 text-left text-sm transition-colors"
                                :class="selectedTypeId === type.id ? 'coa-type-btn--active' : 'hover:bg-erp-page'"
                            >
                                <span class="font-medium" x-text="type.name"></span>
                                <span class="ml-1 tabular-nums text-[11px] text-slate-500" x-text="'(' + type.account_count + ')'"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </aside>

            <section
                class="coa-explorer__panel coa-explorer__panel--groups"
                :class="{ 'coa-explorer__panel--hidden-mobile': mobileStack !== 'groups' }"
            >
                <h2 class="coa-explorer__panel-title">{{ __('Account groups') }}</h2>
                <div x-show="groupsLoading" class="py-8 text-center text-xs text-slate-500">{{ __('Loading…') }}</div>
                <div x-show="!groupsLoading && groups.length === 0" x-cloak class="py-8 text-center text-xs text-slate-500">{{ __('No groups for this type.') }}</div>
                <div class="grid gap-2 sm:grid-cols-2" x-show="!groupsLoading && groups.length > 0">
                    <template x-for="group in groups" :key="group.id">
                        <button
                            type="button"
                            @click="selectGroup(group.id)"
                            class="coa-group-card rounded-lg border px-3 py-2.5 text-left transition-colors"
                            :class="selectedGroupId === group.id ? 'coa-group-card--active' : 'border-erp-border bg-white hover:border-erp-accent/40'"
                        >
                            <span class="font-mono text-[11px] text-slate-400" x-text="group.code"></span>
                            <span class="mt-0.5 block text-sm font-semibold text-erp-primary" x-text="group.name"></span>
                            <span class="mt-1 block text-[10px] text-slate-500" x-text="group.account_count + ' {{ __('accounts') }}'"></span>
                        </button>
                    </template>
                </div>
            </section>

            <section
                class="coa-explorer__panel coa-explorer__panel--accounts"
                :class="{ 'coa-explorer__panel--hidden-mobile': mobileStack !== 'accounts' }"
            >
                <h2 class="coa-explorer__panel-title">{{ __('Accounts') }}</h2>
                <div x-show="accountsLoading" class="py-8 text-center text-xs text-slate-500">{{ __('Loading…') }}</div>
                <div x-show="!accountsLoading && !selectedGroupId" x-cloak class="py-8 text-center text-xs text-slate-500">{{ __('Select a group to view accounts.') }}</div>
                <div x-show="!accountsLoading && selectedGroupId && accounts.length === 0" x-cloak class="py-8 text-center text-xs text-slate-500">{{ __('No accounts in this group.') }}</div>
                <div class="overflow-x-auto" x-show="!accountsLoading && accounts.length > 0">
                    <table class="erp-table erp-table--grid w-full text-sm">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Code') }}</th>
                                <th scope="col">{{ __('Name') }}</th>
                                <th scope="col" class="hidden sm:table-cell">{{ __('Normal balance') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col" class="text-right">{{ __('Transactions') }}</th>
                                <th scope="col" class="erp-table-actions-col"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-erp-border bg-white">
                            <template x-for="row in accounts" :key="row.id">
                                <tr
                                    class="cursor-pointer transition-colors hover:bg-slate-50/80"
                                    :class="{ 'bg-erp-accent/5': selectedAccountId === row.id }"
                                    @click="openDrawer(row.id)"
                                >
                                    <td class="py-2 font-mono text-[11px] text-erp-accent" :style="'padding-left:' + (0.75 + row.depth * 0.75) + 'rem'" x-text="row.code"></td>
                                    <td class="py-2 font-medium text-erp-primary" x-text="row.name"></td>
                                    <td class="hidden py-2 text-xs text-slate-500 sm:table-cell" x-text="row.normal_balance"></td>
                                    <td class="py-2">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700': row.status === 'active',
                                                'bg-slate-100 text-slate-600': row.status === 'inactive',
                                                'bg-amber-50 text-amber-700': row.status === 'locked',
                                            }"
                                            x-text="row.status_label"
                                        ></span>
                                    </td>
                                    <td class="py-2 text-right tabular-nums text-xs text-slate-600" x-text="row.transactions_count"></td>
                                    <td class="erp-table-actions-col py-2" @click.stop>
                                        <div class="relative inline-block text-left" x-data="erpFloatingMenu('right')" @click.outside="close()" @keydown.escape.window="close()">
                                            <button type="button" x-ref="trigger" @click.stop="toggle($event)" class="erp-row-actions-trigger inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-erp-page" aria-label="{{ __('Row actions') }}">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" /></svg>
                                            </button>
                                            <div x-ref="menu" x-show="open" x-cloak :style="menuStyle" @click="close()" class="erp-row-actions-menu min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 text-sm shadow-lg" role="menu">
                                                <button type="button" @click="openDrawer(row.id); close()" class="flex w-full px-3 py-2 text-left text-slate-700 hover:bg-erp-page">{{ __('View') }}</button>
                                                <template x-if="permissions.edit">
                                                    <a :href="row.urls.edit" data-turbo-frame="erp-main" class="flex w-full px-3 py-2 text-slate-700 hover:bg-erp-page">{{ __('Edit') }}</a>
                                                </template>
                                                <template x-if="permissions.create">
                                                    <a :href="row.urls.create_child" data-turbo-frame="erp-main" class="flex w-full px-3 py-2 text-slate-700 hover:bg-erp-page">{{ __('Add child') }}</a>
                                                </template>
                                                <template x-if="permissions.ledger">
                                                    <a :href="row.urls.ledger" data-turbo-frame="erp-main" class="flex w-full px-3 py-2 text-slate-700 hover:bg-erp-page">{{ __('Ledger') }}</a>
                                                </template>
                                                <template x-if="permissions.edit && row.status === 'active'">
                                                    <button type="button" @click="deactivateAccount(row.id); close()" class="flex w-full px-3 py-2 text-left text-amber-700 hover:bg-erp-page">{{ __('Deactivate') }}</button>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40 bg-erp-primary/40" @click="closeDrawer()" aria-hidden="true"></div>
        <aside
            x-show="drawerOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            class="coa-account-drawer"
            @keydown.escape.window="closeDrawer()"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                <div class="min-w-0">
                    <p class="font-mono text-[11px] text-erp-accent" x-text="panel?.code"></p>
                    <h2 class="truncate text-base font-semibold text-erp-primary" x-text="panel?.name"></h2>
                </div>
                <button type="button" @click="closeDrawer()" class="rounded-lg p-1.5 text-slate-500 hover:bg-erp-page" aria-label="{{ __('Close') }}">
                    <x-admin.icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div x-show="panelLoading" class="p-4 text-sm text-slate-500">{{ __('Loading…') }}</div>

            <div x-show="panel && !panelLoading" class="flex flex-1 flex-col overflow-y-auto p-4">
                <dl class="space-y-2.5 text-sm">
                    <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd x-text="panel?.type"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Group') }}</dt><dd x-text="panel?.group || '—'"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Normal balance') }}</dt><dd x-text="panel?.normal_balance"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Current balance') }}</dt><dd class="font-mono font-semibold tabular-nums" x-text="panel?.current_balance_formatted"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd x-text="panel?.status_label"></dd></div>
                </dl>

                <h3 class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Recent transactions') }}</h3>
                <template x-if="panel?.recent_transactions?.length === 0">
                    <p class="text-xs text-slate-500">{{ __('No posted transactions yet.') }}</p>
                </template>
                <ul class="space-y-2" x-show="panel?.recent_transactions?.length > 0">
                    <template x-for="(txn, idx) in panel?.recent_transactions ?? []" :key="idx">
                        <li class="rounded-md border border-erp-border px-2.5 py-2 text-xs">
                            <div class="flex justify-between gap-2">
                                <span class="font-mono text-erp-accent" x-text="txn.journal_number"></span>
                                <span class="text-slate-500" x-text="txn.journal_date"></span>
                            </div>
                            <div class="mt-1 flex justify-between tabular-nums text-slate-600">
                                <span x-show="txn.debit > 0" x-text="'Dr ' + Number(txn.debit).toFixed(2)"></span>
                                <span x-show="txn.credit > 0" x-text="'Cr ' + Number(txn.credit).toFixed(2)"></span>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-auto flex flex-wrap gap-2 border-t border-erp-border pt-4">
                    <template x-if="permissions.ledger && panel?.urls?.ledger">
                        <a :href="panel.urls.ledger" data-turbo-frame="erp-main" class="erp-btn-secondary text-xs">{{ __('View ledger') }}</a>
                    </template>
                    <template x-if="permissions.edit && panel?.urls?.edit">
                        <a :href="panel.urls.edit" data-turbo-frame="erp-main" class="erp-btn-secondary text-xs">{{ __('Edit') }}</a>
                    </template>
                    <template x-if="permissions.create && panel?.urls?.create_child">
                        <a :href="panel.urls.create_child" data-turbo-frame="erp-main" class="erp-btn-secondary text-xs">{{ __('Create child') }}</a>
                    </template>
                    <template x-if="permissions.edit && panel?.status === 'active'">
                        <button type="button" @click="deactivateAccount()" class="erp-btn-secondary text-xs text-amber-700">{{ __('Deactivate') }}</button>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</x-admin-layout>
