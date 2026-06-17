@props(['filters', 'branches', 'customers', 'job_cards', 'production_types'])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('admin.production.reports.index')" :reset-url="route('admin.production.reports.index')">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'job_profitability' }}">
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select id="customer_id" name="customer_id" class="erp-toolbar-select" aria-label="{{ __('Customer') }}">
            <option value="">{{ __('All customers') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->company_name }}</option>
            @endforeach
        </select>
        <select id="production_type" name="production_type" class="erp-toolbar-select" aria-label="{{ __('Product / department') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach ($production_types as $type)
                <option value="{{ $type->value }}" @selected(($filters['production_type'] ?? null) === $type->value)>{{ str($type->value)->headline() }}</option>
            @endforeach
        </select>
        <select id="job_card_id" name="job_card_id" class="erp-toolbar-select" aria-label="{{ __('Job') }}">
            <option value="">{{ __('All jobs') }}</option>
            @foreach ($job_cards as $jobCard)
                <option value="{{ $jobCard->id }}" @selected(($filters['job_card_id'] ?? null) == $jobCard->id)>{{ $jobCard->job_card_number }}</option>
            @endforeach
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('Search job number…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Job number search') }}"
        >
    </x-admin.index-toolbar>
</x-admin.card>
