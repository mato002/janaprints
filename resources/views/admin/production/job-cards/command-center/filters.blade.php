@php
    $indexUrl = route('admin.production.job-cards.index');
@endphp

<x-admin.card :padding="false">
    <form method="GET" action="{{ $indexUrl }}" class="border-b border-erp-border px-4 py-3">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Search') }}</label>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="erp-input w-full text-sm" placeholder="{{ __('Job, customer, SO, product…') }}" />
            </div>
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
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Work center') }}</label>
                <select name="work_center_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All work centers') }}</option>
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

        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
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

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply filters') }}</button>
            <a href="{{ $indexUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
        </div>
    </form>

    @if (count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 border-t border-erp-border px-4 py-2">
            <span class="text-xs font-medium text-slate-500">{{ __('Active filters') }}:</span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="erp-main">
                    {{ $chip['label'] }}
                    <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif
</x-admin.card>
