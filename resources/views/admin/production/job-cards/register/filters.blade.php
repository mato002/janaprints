@php
    $indexUrl = route('admin.production.job-cards.index');
@endphp

<div class="job-cards-register-filters sticky top-0 z-20 -mx-1 mb-4 space-y-3 rounded-lg border border-erp-border bg-erp-page/95 px-1 py-3 backdrop-blur supports-[backdrop-filter]:bg-erp-page/80">
    <nav class="job-cards-register-filters__tabs flex flex-nowrap gap-1.5 overflow-x-auto" aria-label="{{ __('Production stage') }}">
        @foreach ($statusTabs as $tab)
            <a
                href="{{ $tab['url'] }}"
                @class([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $tab['active'],
                ])
                data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}"
            >{{ $tab['label'] }}</a>
        @endforeach
    </nav>

    <form
        method="GET"
        action="{{ $indexUrl }}"
        class="erp-index-toolbar-form"
        data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}"
    >
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1 sm:max-w-md">
                <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    id="job-cards-search"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="erp-input w-full py-2 pl-9 text-sm"
                    placeholder="{{ __('Job number, customer, order, product…') }}"
                    aria-label="{{ __('Search') }}"
                    data-erp-auto-search
                />
            </div>

            <x-admin.filter-sheet>
                @if (filled($filters['stage'] ?? null))
                    <input type="hidden" name="stage" value="{{ $filters['stage'] }}">
                @endif

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Status') }}</span>
                    <select name="status" class="erp-input w-full text-sm" aria-label="{{ __('Status') }}">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach ($filterOptions['statuses'] ?? [] as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str_replace('_', ' ', $status->value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Priority') }}</span>
                    <select name="priority" class="erp-input w-full text-sm" aria-label="{{ __('Priority') }}">
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach ($filterOptions['priorities'] ?? [] as $priority)
                            <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Customer') }}</span>
                    <select name="customer_id" class="erp-input w-full text-sm" aria-label="{{ __('Customer') }}">
                        <option value="">{{ __('All customers') }}</option>
                        @foreach ($filterOptions['customers'] ?? [] as $customer)
                            <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Sales order') }}</span>
                    <select name="sales_order_id" class="erp-input w-full text-sm" aria-label="{{ __('Sales order') }}">
                        <option value="">{{ __('All orders') }}</option>
                        @foreach ($filterOptions['sales_orders'] ?? [] as $order)
                            <option value="{{ $order->id }}" @selected(($filters['sales_order_id'] ?? null) == $order->id)>{{ $order->order_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Department') }}</span>
                    <select name="work_center_id" class="erp-input w-full text-sm" aria-label="{{ __('Department') }}">
                        <option value="">{{ __('All departments') }}</option>
                        @foreach ($filterOptions['work_centers'] ?? [] as $center)
                            <option value="{{ $center->id }}" @selected(($filters['work_center_id'] ?? null) == $center->id)>{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Date from') }}</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input w-full text-sm" aria-label="{{ __('Date from') }}" />
                </div>

                <div class="erp-filter-sheet__field">
                    <span class="erp-filter-sheet__label">{{ __('Date to') }}</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input w-full text-sm" aria-label="{{ __('Date to') }}" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="due_today" value="1" @checked($filters['due_today'] ?? false) class="rounded border-slate-300" />
                    {{ __('Due today') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="overdue" value="1" @checked($filters['overdue'] ?? false) class="rounded border-slate-300" />
                    {{ __('Overdue') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="awaiting_qc" value="1" @checked($filters['awaiting_qc'] ?? false) class="rounded border-slate-300" />
                    {{ __('Awaiting QC') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="ready_dispatch" value="1" @checked($filters['ready_dispatch'] ?? false) class="rounded border-slate-300" />
                    {{ __('Ready dispatch') }}
                </label>
            </x-admin.filter-sheet>

            <select id="saved-view" class="erp-select text-sm" @change="applyPreset($event.target.value)" aria-label="{{ __('Saved view') }}">
                <option value="">{{ __('Saved view') }}</option>
                @foreach ($savedViewPresets as $preset)
                    <option value="{{ $preset['key'] }}">{{ $preset['label'] }}</option>
                @endforeach
                <template x-for="view in customViews" :key="view.id">
                    <option :value="view.id" x-text="view.label"></option>
                </template>
            </select>

            <button type="button" class="erp-btn-secondary text-sm" @click="saveCurrentView()">{{ __('Save view') }}</button>

            <div class="relative" @click.outside="columnsOpen = false">
                <button type="button" class="erp-btn-secondary text-sm" @click="columnsOpen = !columnsOpen">
                    {{ __('Columns') }}
                </button>
                <div
                    x-show="columnsOpen"
                    x-cloak
                    class="absolute end-0 z-30 mt-1 min-w-[12rem] rounded-lg border border-erp-border bg-white py-2 shadow-lg"
                >
                    <template x-for="column in columns" :key="column.key">
                        <label class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-sm hover:bg-slate-50">
                            <input type="checkbox" class="rounded border-slate-300" :checked="isColumnVisible(column.key)" @change="toggleColumn(column.key)">
                            <span x-text="column.label"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>
    </form>

    @if (count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2">
            <span class="text-xs font-medium text-slate-500">{{ __('Active filters') }}:</span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}">
                    {{ $chip['label'] }}
                    <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
