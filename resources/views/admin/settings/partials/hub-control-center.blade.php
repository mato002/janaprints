@php
    $summary = $controlCenter['summary'];
    $scopeAction = route('admin.settings.show', 'hub');
@endphp

<div
    x-data="settingsControlCenter(@js($controlCenter['cards']))"
    x-cloak
    class="settings-workspace w-full min-w-0"
>
    {{-- Compact toolbar (~120px) --}}
    <div class="settings-workspace-toolbar mb-3 space-y-2 rounded-lg border border-erp-border bg-white px-3 py-2.5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-base font-semibold text-erp-primary">{{ __('Settings Control Center') }}</h1>

            <div class="flex flex-wrap items-center gap-2">
                @if ($companies->count() > 1 || $branches->isNotEmpty())
                    <form method="GET" action="{{ $scopeAction }}" class="flex flex-wrap items-center gap-2">
                        @if ($companies->count() > 1)
                            <label class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                <span>{{ __('Company') }}</span>
                                <select name="company_id" class="erp-select py-1 pl-2 pr-7 text-xs" onchange="this.form.submit()">
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @else
                            <input type="hidden" name="company_id" value="{{ $companyId }}">
                        @endif

                        @if ($branches->isNotEmpty())
                            <label class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                <span>{{ __('Branch') }}</span>
                                <select name="branch_id" class="erp-select py-1 pl-2 pr-7 text-xs" onchange="this.form.submit()">
                                    <option value="">{{ __('All branches') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                    </form>
                @endif

                <div class="inline-flex rounded-md border border-erp-border bg-erp-page/50 p-0.5 text-[11px]">
                    <button
                        type="button"
                        class="rounded px-2 py-1 font-medium transition-colors"
                        :class="viewMode === 'grid' ? 'bg-white text-erp-primary shadow-sm' : 'text-slate-500 hover:text-erp-primary'"
                        @click="setViewMode('grid')"
                    >
                        {{ __('Grid') }}
                    </button>
                    <button
                        type="button"
                        class="rounded px-2 py-1 font-medium transition-colors"
                        :class="viewMode === 'list' ? 'bg-white text-erp-primary shadow-sm' : 'text-slate-500 hover:text-erp-primary'"
                        @click="setViewMode('list')"
                    >
                        {{ __('List') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-slate-600">
            <span><span class="text-slate-400">{{ __('Areas') }}:</span> <strong class="text-erp-primary">{{ number_format($summary['total_areas']) }}</strong></span>
            <span><span class="text-slate-400">{{ __('Configured') }}:</span> <strong class="text-emerald-700">{{ number_format($summary['configured']) }}</strong></span>
            @if ($summary['needs_attention'] > 0)
                <span><span class="text-slate-400">{{ __('Needs Attention') }}:</span> <strong class="text-amber-700">{{ number_format($summary['needs_attention']) }}</strong></span>
            @endif
            <span><span class="text-slate-400">{{ __('Incomplete') }}:</span> <strong class="text-red-700">{{ number_format($summary['incomplete']) }}</strong></span>
            <span><span class="text-slate-400">{{ __('Pending') }}:</span> <strong class="text-slate-600">{{ number_format($summary['pending_setup']) }}</strong></span>

            <div class="relative min-w-[12rem] flex-1 sm:max-w-md">
                <x-admin.icon name="search" class="pointer-events-none absolute left-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    x-model="query"
                    class="erp-input w-full py-1.5 pl-7 text-xs"
                    placeholder="{{ __('Search settings…') }}"
                    aria-label="{{ __('Search settings') }}"
                >
            </div>
        </div>
    </div>

    {{-- Sticky domain filters --}}
    <div class="sticky top-0 z-20 -mx-1 mb-3 border-b border-erp-border bg-white/95 px-1 pb-2 pt-1 backdrop-blur supports-[backdrop-filter]:bg-white/90">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($controlCenter['filters'] as $filter)
                <button
                    type="button"
                    class="shrink-0 rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                    :class="activeFilter === @js($filter['slug'])
                        ? 'border-erp-accent bg-erp-accent text-white'
                        : 'border-erp-border bg-white text-slate-600 hover:border-erp-accent/40 hover:text-erp-primary'"
                    @click="setFilter(@js($filter['slug']))"
                >
                    {{ $filter['label'] }}
                    <span class="ml-1 opacity-70">{{ $filter['count'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Results --}}
    <div
        x-show="viewMode === 'grid'"
        class="grid w-full grid-cols-[repeat(auto-fill,minmax(11.5rem,1fr))] gap-3"
    >
        @foreach ($controlCenter['cards'] as $card)
            <div class="min-w-0" x-show="cardVisible(@js($card['id']))">
                @include('admin.settings.partials.settings-tile', [
                    'title' => $card['title'],
                    'description' => $card['description'],
                    'icon' => $card['icon'],
                    'href' => $card['href'],
                    'comingSoon' => $card['comingSoon'],
                    'statusLabel' => $card['statusLabel'],
                    'statusVariant' => $card['statusVariant'],
                ])
            </div>
        @endforeach
    </div>

    <div x-show="viewMode === 'list'" x-cloak class="space-y-1.5">
        @foreach ($controlCenter['cards'] as $card)
            <div x-show="cardVisible(@js($card['id']))">
                @include('admin.settings.partials.settings-list-row', [
                    'title' => $card['title'],
                    'description' => $card['description'],
                    'icon' => $card['icon'],
                    'href' => $card['href'],
                    'comingSoon' => $card['comingSoon'],
                    'statusLabel' => $card['statusLabel'],
                    'statusVariant' => $card['statusVariant'],
                    'domainLabel' => $card['domain_label'],
                ])
            </div>
        @endforeach
    </div>

    <p
        x-show="visibleCount === 0"
        x-cloak
        class="rounded-lg border border-dashed border-erp-border px-4 py-8 text-center text-sm text-slate-500"
    >
        {{ __('No settings match your search or filter.') }}
    </p>
</div>
