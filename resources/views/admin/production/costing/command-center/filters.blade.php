@php
    $dashboardUrl = route('admin.production.costing.dashboard');
@endphp

<x-admin.card :padding="false">
    <form method="GET" action="{{ $dashboardUrl }}" class="erp-index-toolbar-form border-b border-erp-border px-3 py-2 sm:px-4" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}">
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.filter-sheet>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Date from') }}">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Date to') }}">
                <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($filterOptions['branches'] ?? [] as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="customer_id" class="erp-toolbar-select" aria-label="{{ __('Customer') }}">
                    <option value="">{{ __('All customers') }}</option>
                    @foreach ($filterOptions['customers'] ?? [] as $customer)
                        <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->company_name }}</option>
                    @endforeach
                </select>
                <select name="production_type" class="erp-toolbar-select" aria-label="{{ __('Product / service type') }}">
                    <option value="">{{ __('All types') }}</option>
                    @foreach ($filterOptions['production_types'] ?? [] as $type)
                        <option value="{{ $type->value }}" @selected(($filters['production_type'] ?? '') === $type->value)>{{ str($type->value)->replace('_', ' ')->headline() }}</option>
                    @endforeach
                </select>
                <select name="margin_category" class="erp-toolbar-select" aria-label="{{ __('Margin category') }}">
                    <option value="">{{ __('All margins') }}</option>
                    @foreach ($filterOptions['margin_categories'] ?? [] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['margin_category'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-admin.filter-sheet>
            <a href="{{ $dashboardUrl }}" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}">{{ __('Reset') }}</a>
        </div>
    </form>

    @if (count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 border-t border-erp-border px-4 py-2">
            <span class="text-xs font-medium text-slate-500">{{ __('Active filters') }}:</span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}">
                    {{ $chip['label'] }}
                    <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif
</x-admin.card>
