@php($fields = $formFields ?? [])
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
    @if (($fields['warehouse_id']['visible'] ?? true))
        <div>
            <label class="erp-label">{{ $fields['warehouse_id']['label'] ?? __('Warehouse') }}</label>
            <select name="warehouse_id" class="erp-input w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected((string) old('warehouse_id', $selectedWarehouseId ?? null) === (string) $w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($type === 'receipt')
        @if (($fields['source']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['source']['label'] ?? __('Source') }}</label>
                <select name="source" class="erp-input w-full" @required($fields['source']['required'] ?? true)>
                    @foreach ($sources as $s)<option value="{{ $s->value }}">{{ $s->value }}</option>@endforeach
                </select>
            </div>
        @endif
        @if (($fields['receipt_date']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['receipt_date']['label'] ?? __('Date') }}</label>
                <input type="date" name="receipt_date" class="erp-input w-full" value="{{ old('receipt_date', now()->toDateString()) }}" @required($fields['receipt_date']['required'] ?? true)>
            </div>
        @endif
    @elseif ($type === 'issue')
        @if (($fields['destination']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['destination']['label'] ?? __('Destination') }}</label>
                <select name="destination" class="erp-input w-full" @required($fields['destination']['required'] ?? true)>
                    @foreach ($destinations as $d)<option value="{{ $d->value }}" @selected(old('destination') === $d->value)>{{ $d->value }}</option>@endforeach
                </select>
            </div>
        @endif
        @if (($fields['issue_date']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['issue_date']['label'] ?? __('Date') }}</label>
                <input type="date" name="issue_date" class="erp-input w-full" value="{{ old('issue_date', now()->toDateString()) }}" @required($fields['issue_date']['required'] ?? true)>
            </div>
        @endif
        @if (($fields['to_warehouse_id']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['to_warehouse_id']['label'] ?? __('To warehouse (transfer)') }}</label>
                <select name="to_warehouse_id" class="erp-input w-full" @required($fields['to_warehouse_id']['required'] ?? false)>
                    <option value="">{{ __('N/A') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string) old('to_warehouse_id') === (string) $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    @else
        @if (($fields['adjustment_date']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['adjustment_date']['label'] ?? __('Date') }}</label>
                <input type="date" name="adjustment_date" class="erp-input w-full" value="{{ old('adjustment_date', now()->toDateString()) }}" @required($fields['adjustment_date']['required'] ?? true)>
            </div>
        @endif
        @if (($fields['reason']['visible'] ?? true))
            <div class="md:col-span-2">
                <label class="erp-label">{{ $fields['reason']['label'] ?? __('Reason') }}</label>
                <input name="reason" class="erp-input w-full" value="{{ old('reason') }}" @required($fields['reason']['required'] ?? true)>
            </div>
        @endif
    @endif
</div>
@if ($type === 'issue' && ($fields['notes']['visible'] ?? true))
    <div class="mt-4 max-w-3xl">
        <label class="erp-label">{{ $fields['notes']['label'] ?? __('Notes') }}</label>
        <textarea name="notes" class="erp-input w-full" rows="2" @required($fields['notes']['required'] ?? false)>{{ old('notes') }}</textarea>
    </div>
@endif
