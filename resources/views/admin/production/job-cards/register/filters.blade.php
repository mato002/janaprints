@php
    $indexUrl = route('admin.production.job-cards.index');
@endphp

<div class="sticky top-0 z-20 -mx-1 mb-4 space-y-3 rounded-lg border border-erp-border bg-erp-page/95 px-1 py-3 backdrop-blur supports-[backdrop-filter]:bg-erp-page/80">
    <nav class="flex flex-wrap gap-1.5" aria-label="{{ __('Production stage') }}">
        @foreach ($statusTabs as $tab)
            <a
                href="{{ $tab['url'] }}"
                @class([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $tab['active'],
                ])
                data-turbo-frame="erp-main"
            >{{ $tab['label'] }}</a>
        @endforeach
    </nav>

    <form
        method="GET"
        action="{{ $indexUrl }}"
        x-data="erpIndexFilterForm()"
        @change="onFieldChange($event)"
        class="space-y-3"
        data-turbo-frame="erp-main"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="min-w-0 flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-600" for="job-cards-search">{{ __('Quick search') }}</label>
                <div class="relative">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        id="job-cards-search"
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="erp-input w-full py-2 pl-9 text-sm"
                        placeholder="{{ __('Job number, customer, order, product…') }}"
                        data-erp-auto-search
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600" for="saved-view">{{ __('Saved view') }}</label>
                    <select id="saved-view" class="erp-select min-w-[10rem] text-sm" @change="applyPreset($event.target.value)">
                        <option value="">{{ __('Choose preset…') }}</option>
                        @foreach ($savedViewPresets as $preset)
                            <option value="{{ $preset['key'] }}">{{ $preset['label'] }}</option>
                        @endforeach
                        <template x-for="view in customViews" :key="view.id">
                            <option :value="view.id" x-text="view.label"></option>
                        </template>
                    </select>
                </div>

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

                <a href="{{ $indexUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
            </div>
        </div>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-2.5 text-sm font-medium text-erp-primary">{{ __('Advanced filters') }}</summary>
            <div class="grid grid-cols-1 gap-3 border-t border-erp-border px-4 py-3 md:grid-cols-2 xl:grid-cols-4">
                @if (filled($filters['stage'] ?? null))
                    <input type="hidden" name="stage" value="{{ $filters['stage'] }}">
                @endif
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Status') }}</label>
                    <select name="status" class="erp-input w-full text-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach ($filterOptions['statuses'] ?? [] as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str_replace('_', ' ', $status->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Priority') }}</label>
                    <select name="priority" class="erp-input w-full text-sm">
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach ($filterOptions['priorities'] ?? [] as $priority)
                            <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Customer') }}</label>
                    <select name="customer_id" class="erp-input w-full text-sm">
                        <option value="">{{ __('All customers') }}</option>
                        @foreach ($filterOptions['customers'] ?? [] as $customer)
                            <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Sales order') }}</label>
                    <select name="sales_order_id" class="erp-input w-full text-sm">
                        <option value="">{{ __('All orders') }}</option>
                        @foreach ($filterOptions['sales_orders'] ?? [] as $order)
                            <option value="{{ $order->id }}" @selected(($filters['sales_order_id'] ?? null) == $order->id)>{{ $order->order_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Department') }}</label>
                    <select name="work_center_id" class="erp-input w-full text-sm">
                        <option value="">{{ __('All departments') }}</option>
                        @foreach ($filterOptions['work_centers'] ?? [] as $center)
                            <option value="{{ $center->id }}" @selected(($filters['work_center_id'] ?? null) == $center->id)>{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Date from') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input w-full text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Date to') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input w-full text-sm" />
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 border-t border-erp-border px-4 py-2 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="due_today" value="1" @checked($filters['due_today'] ?? false) class="rounded border-slate-300" />
                    {{ __('Due today') }}
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="overdue" value="1" @checked($filters['overdue'] ?? false) class="rounded border-slate-300" />
                    {{ __('Overdue') }}
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="awaiting_qc" value="1" @checked($filters['awaiting_qc'] ?? false) class="rounded border-slate-300" />
                    {{ __('Awaiting QC') }}
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="ready_dispatch" value="1" @checked($filters['ready_dispatch'] ?? false) class="rounded border-slate-300" />
                    {{ __('Ready dispatch') }}
                </label>
            </div>
        </details>
    </form>

    @if (count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2">
            <span class="text-xs font-medium text-slate-500">{{ __('Active filters') }}:</span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="erp-main">
                    {{ $chip['label'] }}
                    <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
