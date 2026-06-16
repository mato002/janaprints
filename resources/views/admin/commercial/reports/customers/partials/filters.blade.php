@props(['filters', 'branches', 'salespersons'])

@php
    use App\Enums\CustomerType;
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('commercial.reports.customers.index')" :reset-url="route('commercial.reports.customers.index')" turbo-frame="erp-main">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'summary' }}">
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select id="customer_type" name="customer_type" class="erp-toolbar-select" aria-label="{{ __('Customer type') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach (CustomerType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['customer_type'] ?? '') === $type->value)>{{ ucfirst($type->value) }}</option>
            @endforeach
        </select>
        <select id="salesperson_id" name="salesperson_id" class="erp-toolbar-select" aria-label="{{ __('Salesperson') }}">
            <option value="">{{ __('All salespersons') }}</option>
            @foreach ($salespersons as $salesperson)
                <option value="{{ $salesperson->id }}" @selected(($filters['salesperson_id'] ?? null) == $salesperson->id)>{{ $salesperson->name }}</option>
            @endforeach
        </select>
        <select id="activity_status" name="activity_status" class="erp-toolbar-select" aria-label="{{ __('Activity status') }}">
            <option value="">{{ __('All activity') }}</option>
            <option value="active" @selected(($filters['activity_status'] ?? '') === 'active')>{{ __('Active (ordered in period)') }}</option>
            <option value="inactive" @selected(($filters['activity_status'] ?? '') === 'inactive')>{{ __('Inactive (no orders in period)') }}</option>
            <option value="new" @selected(($filters['activity_status'] ?? '') === 'new')>{{ __('New (created in period)') }}</option>
            <option value="dormant" @selected(($filters['activity_status'] ?? '') === 'dormant')>{{ __('Dormant (no recent orders)') }}</option>
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('Search customers…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Search') }}"
        >
        <x-admin.status-pills
            :options="collect(CustomerStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst($status->value)])->prepend(['value' => '', 'label' => __('All')])->all()"
            param="status"
            :current="$filters['status'] ?? ''"
        />
    </x-admin.index-toolbar>
</x-admin.card>
