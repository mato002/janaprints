@props(['filters', 'branches'])

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('admin.reports.production') }}" data-turbo-frame="erp-main" class="flex flex-wrap items-end gap-3">
        @if (! empty($filters['tab']))
            <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
        @endif
        <div>
            <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-input mt-1">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-input mt-1">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
            <select id="branch_id" name="branch_id" class="erp-input mt-1 min-w-[10rem]">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
    </form>
</x-admin.card>
