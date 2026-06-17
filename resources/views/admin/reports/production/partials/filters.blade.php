@props(['filters', 'branches'])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('admin.reports.production')" :reset-url="route('admin.reports.production')">
        @if (! empty($filters['tab']))
            <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
        @endif
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </x-admin.index-toolbar>
</x-admin.card>
