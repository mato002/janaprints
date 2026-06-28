<x-admin.index-toolbar :action="route('admin.reports.operational-registers')" :reset-url="route('admin.reports.operational-registers', request()->only('embedded'))" compact>
    <select id="preset" name="preset" class="erp-toolbar-select" aria-label="{{ __('Period') }}" data-erp-auto-submit>
        <option value="">{{ __('Custom range') }}</option>
        @foreach ($presets as $key => $label)
            <option value="{{ $key }}" @selected(($filters['preset'] ?? '') === $key)>{{ __($label) }}</option>
        @endforeach
    </select>

    <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}" data-erp-auto-submit>
    <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}" data-erp-auto-submit>

    <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}" data-erp-auto-submit>
        <option value="">{{ __('All branches') }}</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
        @endforeach
    </select>

    <input type="hidden" name="register" value="{{ $filters['register'] ?? request('register', 'daily_sales') }}">

    <input id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="erp-toolbar-input min-w-[8rem] flex-1" placeholder="{{ __('Search…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>

    @if (request('embedded'))
        <input type="hidden" name="embedded" value="1">
    @endif
</x-admin.index-toolbar>
