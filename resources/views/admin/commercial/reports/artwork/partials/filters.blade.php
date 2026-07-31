@props(['filters', 'branches', 'customers', 'designers', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null])

@php
    use App\Enums\ArtworkApprovalDecision;
    use App\Enums\ArtworkRequestStatus;
    $toolbarAction = $filter_action ?? route('admin.commercial.reports.artwork.index');
    $toolbarResetUrl = $filter_reset_url ?? route('admin.commercial.reports.artwork.index');
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$toolbarAction" :reset-url="$toolbarResetUrl">
        @if ($report_key)
            <input type="hidden" name="report" value="{{ $report_key }}">
        @endif
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'requests' }}">
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
        <select id="designer_id" name="designer_id" class="erp-toolbar-select" aria-label="{{ __('Designer') }}">
            <option value="">{{ __('All designers') }}</option>
            @foreach ($designers as $designer)
                <option value="{{ $designer->id }}" @selected(($filters['designer_id'] ?? null) == $designer->id)>{{ $designer->name }}</option>
            @endforeach
        </select>
        <select id="approval_status" name="approval_status" class="erp-toolbar-select" aria-label="{{ __('Approval status') }}">
            <option value="">{{ __('All approval outcomes') }}</option>
            @foreach (ArtworkApprovalDecision::cases() as $decision)
                <option value="{{ $decision->value }}" @selected(($filters['approval_status'] ?? '') === $decision->value)>{{ ucfirst(str_replace('_', ' ', $decision->value)) }}</option>
            @endforeach
        </select>
        <select id="delay_status" name="delay_status" class="erp-toolbar-select" aria-label="{{ __('Delay status') }}">
            <option value="">{{ __('All delay states') }}</option>
            <option value="delayed" @selected(($filters['delay_status'] ?? '') === 'delayed')>{{ __('Delayed') }}</option>
            <option value="on_time" @selected(($filters['delay_status'] ?? '') === 'on_time')>{{ __('On time') }}</option>
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('Request number or title…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Search') }}"
        >
        <x-admin.status-pills
            :options="collect(ArtworkRequestStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all()"
            param="status"
            :current="$filters['status'] ?? ''"
        />
    </x-admin.index-toolbar>
</x-admin.card>
