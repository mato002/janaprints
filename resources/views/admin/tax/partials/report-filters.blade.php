<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
        <select name="tax_period_id" class="erp-toolbar-select" aria-label="{{ __('Tax period') }}">
            <option value="">{{ __('Custom range') }}</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected(($filters['tax_period_id'] ?? null) == $period->id)>
                    {{ $period->code }} ({{ $period->start_date->format('Y-m-d') }} – {{ $period->end_date->format('Y-m-d') }})
                </option>
            @endforeach
        </select>
        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
    </x-admin.index-toolbar>
</x-admin.card>
