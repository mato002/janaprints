@php
    $summary = $controlCenter['summary'];
    $health = $controlCenter['health'];
    $auditRoute = Route::has('admin.security.audit.index') ? route('admin.security.audit.index') : null;
@endphp

<div
    x-data="formsControlCenter(@js([
        'cards' => $controlCenter['cards'],
        'exportPayload' => $controlCenter['export_payload'],
        'auditUrl' => $auditRoute,
    ]))"
    x-cloak
    class="forms-control-center w-full min-w-0"
>
    {{-- Executive KPI strip --}}
    <div class="erp-stats-strip forms-kpi-strip mb-3 rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-sm">
        <span>
            <span class="text-slate-400">{{ __('Total Forms') }}:</span>
            <strong class="text-erp-primary">{{ number_format($summary['total_forms']) }}</strong>
        </span>
        <span>
            <span class="text-slate-400">{{ __('Active Forms') }}:</span>
            <strong class="text-emerald-700">{{ number_format($summary['active_forms']) }}</strong>
        </span>
        <span>
            <span class="text-slate-400">{{ __('Planned Forms') }}:</span>
            <strong class="text-slate-600">{{ number_format($summary['planned_forms']) }}</strong>
        </span>
        <span>
            <span class="text-slate-400">{{ __('Total Managed Fields') }}:</span>
            <strong class="text-erp-accent">{{ number_format($summary['managed_fields']) }}</strong>
        </span>
    </div>

    <div class="forms-control-layout">
        {{-- Left category navigation (desktop sidebar / mobile pills) --}}
        <aside class="forms-control-nav" aria-label="{{ __('Form categories') }}">
            <p class="mb-2 hidden text-[10px] font-semibold uppercase tracking-wide text-slate-400 lg:block">{{ __('Categories') }}</p>

            <div class="forms-control-nav-list">
                <button
                    type="button"
                    class="forms-control-nav-item"
                    :class="activeCategory === 'all' ? 'forms-control-nav-item--active' : ''"
                    @click="setCategory('all')"
                >
                    <x-admin.icon name="view-grid" class="h-4 w-4 shrink-0" />
                    <span class="min-w-0 truncate">{{ __('All Forms') }}</span>
                    <span class="ml-auto shrink-0 text-[10px] opacity-70">{{ $summary['total_forms'] }}</span>
                </button>

                @foreach ($controlCenter['categories'] as $category)
                    <button
                        type="button"
                        class="forms-control-nav-item"
                        :class="activeCategory === @js($category['slug']) ? 'forms-control-nav-item--active' : ''"
                        @click="setCategory(@js($category['slug']))"
                        title="{{ $category['description'] }}"
                    >
                        <x-admin.icon :name="$category['icon']" class="h-4 w-4 shrink-0" />
                        <span class="min-w-0 truncate">{{ $category['label'] }}</span>
                        <span class="ml-auto shrink-0 text-[10px] opacity-70">{{ $category['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Main content --}}
        <div class="forms-control-main min-w-0">
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative min-w-0 flex-1">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                    <input
                        type="search"
                        x-model="query"
                        class="erp-input w-full py-2 pl-8 text-sm"
                        placeholder="{{ __('Search forms, fields, or modules…') }}"
                        aria-label="{{ __('Search forms') }}"
                        autocomplete="off"
                    >
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <button type="button" class="forms-quick-action" @click="exportConfiguration()">
                        <x-admin.icon name="download" class="h-3.5 w-3.5" />
                        {{ __('Export Configuration') }}
                    </button>
                    <button type="button" class="forms-quick-action" @click="importModalOpen = true">
                        <x-admin.icon name="archive" class="h-3.5 w-3.5" />
                        {{ __('Import Configuration') }}
                    </button>
                    <button type="button" class="forms-quick-action" @click="auditForms()">
                        <x-admin.icon name="shield-check" class="h-3.5 w-3.5" />
                        {{ __('Audit Forms') }}
                    </button>
                </div>
            </div>

            <p
                x-show="auditMode"
                x-cloak
                class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"
            >
                {{ __('Audit mode: showing forms with configuration governance issues only.') }}
                <button type="button" class="ml-2 font-semibold underline" @click="auditMode = false">{{ __('Clear') }}</button>
            </p>

            <div class="forms-control-body">
                <div class="forms-control-cards min-w-0">
                    {{-- Active forms by category --}}
                    @foreach ($controlCenter['categories'] as $category)
                        <section
                            class="mb-6"
                            x-show="sectionVisible(@js($category['slug']))"
                            x-cloak
                        >
                            <div class="mb-3 flex items-center gap-2">
                                <x-admin.icon :name="$category['icon']" class="h-4 w-4 text-slate-400" />
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $category['label'] }}</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                @foreach ($controlCenter['active_cards'] as $card)
                                    @if ($card['category_slug'] === $category['slug'])
                                        <div x-show="cardVisible(@js($card['id']))" x-cloak>
                                            @include('admin.settings.forms.partials.form-card', ['card' => $card])
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    {{-- Planned forms section --}}
                    <section class="mb-4" x-show="plannedSectionVisible()" x-cloak>
                        <div class="mb-3 flex items-center justify-between gap-2 border-t border-erp-border pt-4">
                            <div class="flex items-center gap-2">
                                <x-admin.icon name="clock" class="h-4 w-4 text-slate-400" />
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Planned Forms') }}</h2>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                {{ number_format($summary['planned_forms']) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach ($controlCenter['planned_cards'] as $card)
                                <div x-show="cardVisible(@js($card['id']))" x-cloak>
                                    @include('admin.settings.forms.partials.form-card', ['card' => $card])
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <p
                        x-show="visibleCount === 0"
                        x-cloak
                        class="rounded-lg border border-dashed border-erp-border px-4 py-8 text-center text-sm text-slate-500"
                    >
                        {{ __('No forms match your search or filter.') }}
                    </p>
                </div>

                {{-- Right sidebar widgets --}}
                <aside class="forms-control-widgets space-y-3">
                    {{-- Configuration Health --}}
                    <div id="forms-health-widget" class="rounded-xl border border-erp-border bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Configuration Health') }}</h3>
                            @if ($health['healthy'])
                                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    {{ __('Healthy') }}
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20">
                                    {{ __('Attention') }}
                                </span>
                            @endif
                        </div>

                        <dl class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500">{{ __('Missing required fields') }}</dt>
                                <dd @class([
                                    'font-semibold tabular-nums',
                                    'text-amber-700' => $health['missing_required'] > 0,
                                    'text-slate-700' => $health['missing_required'] === 0,
                                ])>{{ number_format($health['missing_required']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500">{{ __('Hidden required fields') }}</dt>
                                <dd @class([
                                    'font-semibold tabular-nums',
                                    'text-red-700' => $health['hidden_required'] > 0,
                                    'text-slate-700' => $health['hidden_required'] === 0,
                                ])>{{ number_format($health['hidden_required']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500">{{ __('Inactive forms') }}</dt>
                                <dd @class([
                                    'font-semibold tabular-nums',
                                    'text-amber-700' => $health['inactive_forms'] > 0,
                                    'text-slate-700' => $health['inactive_forms'] === 0,
                                ])>{{ number_format($health['inactive_forms']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 border-t border-erp-border/70 pt-2">
                                <dt class="font-medium text-erp-primary">{{ __('Governance issues') }}</dt>
                                <dd @class([
                                    'font-semibold tabular-nums',
                                    'text-red-700' => $health['governance_issues'] > 0,
                                    'text-emerald-700' => $health['governance_issues'] === 0,
                                ])>{{ number_format($health['governance_issues']) }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Recently Modified --}}
                    <div class="rounded-xl border border-erp-border bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Recently Modified') }}</h3>

                        @if (count($controlCenter['recently_modified']) === 0)
                            <p class="text-xs text-slate-400">{{ __('No configuration changes recorded yet.') }}</p>
                        @else
                            <ul class="space-y-2">
                                @foreach ($controlCenter['recently_modified'] as $item)
                                    <li>
                                        <a
                                            href="{{ $item['href'] }}"
                                            data-turbo-action="advance"
                                            class="group block rounded-lg border border-transparent px-2 py-1.5 transition-colors hover:border-erp-border hover:bg-erp-page/50"
                                        >
                                            <p class="truncate text-xs font-semibold text-erp-primary group-hover:text-erp-accent">{{ $item['title'] }}</p>
                                            <p class="mt-0.5 truncate text-[10px] text-slate-400">
                                                {{ $item['category_label'] }} · {{ $item['updated_label'] }}
                                            </p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- Import modal --}}
    <div
        x-show="importModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="forms-import-title"
    >
        <div class="absolute inset-0 bg-slate-900/40" @click="importModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-xl border border-erp-border bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    <h2 id="forms-import-title" class="text-sm font-semibold text-erp-primary">{{ __('Import Configuration') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Upload a previously exported forms configuration snapshot.') }}</p>
                </div>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-erp-page hover:text-slate-600" @click="importModalOpen = false">
                    <x-admin.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <input
                type="file"
                accept="application/json,.json"
                class="erp-input w-full text-xs"
                @change="handleImportSelect($event)"
            >

            <p x-show="importMessage" x-text="importMessage" x-cloak class="mt-3 text-xs text-amber-800"></p>
            <p class="mt-2 text-[10px] text-slate-400">{{ __('Import applies server-side configuration changes and is not yet enabled from this screen.') }}</p>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="erp-btn-secondary text-xs" @click="importModalOpen = false">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
