@php
    $dashboardUrl = route('admin.production.costing.dashboard');
@endphp

<x-admin.card :padding="false">
    <form method="GET" action="{{ $dashboardUrl }}" class="border-b border-erp-border px-4 py-3">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Date from') }}</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input w-full text-sm" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Date to') }}</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input w-full text-sm" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Branch') }}</label>
                <select name="branch_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($filterOptions['branches'] ?? [] as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
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
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Product / service type') }}</label>
                <select name="production_type" class="erp-input w-full text-sm">
                    <option value="">{{ __('All types') }}</option>
                    @foreach ($filterOptions['production_types'] ?? [] as $type)
                        <option value="{{ $type->value }}" @selected(($filters['production_type'] ?? '') === $type->value)>{{ str($type->value)->replace('_', ' ')->headline() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Margin category') }}</label>
                <select name="margin_category" class="erp-input w-full text-sm">
                    <option value="">{{ __('All margins') }}</option>
                    @foreach ($filterOptions['margin_categories'] ?? [] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['margin_category'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply filters') }}</button>
            <a href="{{ $dashboardUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
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
