<form method="GET" class="flex flex-wrap items-end gap-3">
    <div>
        <label class="text-[11px] text-slate-500">{{ __('Tax period') }}</label>
        <select name="tax_period_id" class="erp-input mt-1">
            <option value="">{{ __('Custom range') }}</option>
            @foreach ($periods as $period)
                <option value="{{ $period->id }}" @selected(($filters['tax_period_id'] ?? null) == $period->id)>{{ $period->code }} ({{ $period->start_date->format('Y-m-d') }} – {{ $period->end_date->format('Y-m-d') }})</option>
            @endforeach
        </select>
    </div>
    <div><label class="text-[11px] text-slate-500">{{ __('From') }}</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-input mt-1"></div>
    <div><label class="text-[11px] text-slate-500">{{ __('To') }}</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-input mt-1"></div>
    <button class="erp-btn-primary">{{ __('Run report') }}</button>
</form>
