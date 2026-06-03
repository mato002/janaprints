<div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
    <div><label class="erp-label">{{ __('Warehouse') }}</label>
        <select name="warehouse_id" class="erp-input w-full" required>@foreach ($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    @if ($type === 'receipt')
        <div><label class="erp-label">{{ __('Source') }}</label>
            <select name="source" class="erp-input w-full" required>@foreach ($sources as $s)<option value="{{ $s->value }}">{{ $s->value }}</option>@endforeach</select></div>
        <div><label class="erp-label">{{ __('Date') }}</label><input type="date" name="receipt_date" class="erp-input w-full" value="{{ now()->toDateString() }}" required></div>
    @elseif ($type === 'issue')
        <div><label class="erp-label">{{ __('Destination') }}</label>
            <select name="destination" class="erp-input w-full" required>@foreach ($destinations as $d)<option value="{{ $d->value }}">{{ $d->value }}</option>@endforeach</select></div>
        <div><label class="erp-label">{{ __('Date') }}</label><input type="date" name="issue_date" class="erp-input w-full" value="{{ now()->toDateString() }}" required></div>
        <div><label class="erp-label">{{ __('To warehouse (transfer)') }}</label>
            <select name="to_warehouse_id" class="erp-input w-full"><option value="">{{ __('N/A') }}</option>@foreach ($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
    @else
        <div><label class="erp-label">{{ __('Date') }}</label><input type="date" name="adjustment_date" class="erp-input w-full" value="{{ now()->toDateString() }}" required></div>
        <div class="md:col-span-2"><label class="erp-label">{{ __('Reason') }}</label><input name="reason" class="erp-input w-full" required></div>
    @endif
</div>
