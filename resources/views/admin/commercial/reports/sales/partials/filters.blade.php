@props(['filters', 'branches', 'customers', 'salespersons', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null])

@php
    use App\Enums\SalesOrderStatus;
    $toolbarAction = $filter_action ?? route('admin.commercial.reports.sales.index');
    $toolbarResetUrl = $filter_reset_url ?? route('admin.commercial.reports.sales.index');
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$toolbarAction" :reset-url="$toolbarResetUrl">
        @if ($report_key)
            <input type="hidden" name="report" value="{{ $report_key }}">
        @endif
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'summary' }}">
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
        <select id="salesperson_id" name="salesperson_id" class="erp-toolbar-select" aria-label="{{ __('Salesperson') }}">
            <option value="">{{ __('All salespersons') }}</option>
            @foreach ($salespersons as $salesperson)
                <option value="{{ $salesperson->id }}" @selected(($filters['salesperson_id'] ?? null) == $salesperson->id)>{{ $salesperson->name }}</option>
            @endforeach
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('Order number or customer name…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Search') }}"
        >
        <x-admin.status-pills
            :options="collect(SalesOrderStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all()"
            param="status"
            :current="$filters['status'] ?? ''"
        />
    </x-admin.index-toolbar>
</x-admin.card>
