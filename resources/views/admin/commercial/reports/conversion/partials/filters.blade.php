@props(['filters', 'branches', 'lead_sources', 'salespersons'])

@php
    use App\Enums\CustomerType;
    use App\Enums\LeadStatus;
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('commercial.reports.conversion.index')" :reset-url="route('commercial.reports.conversion.index')">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'full_funnel' }}">
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select id="salesperson_id" name="salesperson_id" class="erp-toolbar-select" aria-label="{{ __('Salesperson') }}">
            <option value="">{{ __('All salespersons') }}</option>
            @foreach ($salespersons as $salesperson)
                <option value="{{ $salesperson->id }}" @selected(($filters['salesperson_id'] ?? null) == $salesperson->id)>{{ $salesperson->name }}</option>
            @endforeach
        </select>
        <select id="lead_source_id" name="lead_source_id" class="erp-toolbar-select" aria-label="{{ __('Lead source') }}">
            <option value="">{{ __('All sources') }}</option>
            @foreach ($lead_sources as $source)
                <option value="{{ $source->id }}" @selected(($filters['lead_source_id'] ?? null) == $source->id)>{{ $source->name }}</option>
            @endforeach
        </select>
        <select id="customer_type" name="customer_type" class="erp-toolbar-select" aria-label="{{ __('Customer type') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach (CustomerType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['customer_type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>
            @endforeach
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('Lead, quote, or order reference…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Search') }}"
        >
        <x-admin.status-pills
            :options="collect(LeadStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst($status->value)])->prepend(['value' => '', 'label' => __('All statuses')])->all()"
            param="status"
            :current="$filters['status'] ?? ''"
        />
    </x-admin.index-toolbar>
</x-admin.card>
