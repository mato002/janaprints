@php
    $editable = auth()->user()->can('update', $count);
@endphp

<x-admin.modal-form :title="$count->count_number" maxWidth="5xl">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge">{{ $count->status->value }}</span>
            <span class="text-sm text-slate-600">{{ $count->warehouse?->name }} · {{ $count->count_date->format('d M Y') }}</span>
        </div>

        @if ($editable)
            <form method="POST" action="{{ route('admin.inventory.stock-counts.worksheet.update', $count) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="from" value="store-desk">
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('System qty') }}</th>
                                <th>{{ __('Counted qty') }}</th>
                                <th>{{ __('Reason code') }}</th>
                                <th>{{ __('Comment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($count->items as $index => $line)
                                <tr>
                                    <td>
                                        {{ $line->inventoryItem?->item_name }}
                                        <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $line->inventory_item_id }}">
                                    </td>
                                    <td>{{ $line->system_quantity }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            name="items[{{ $index }}][counted_quantity]"
                                            value="{{ old('items.'.$index.'.counted_quantity', $line->counted_quantity) }}"
                                            class="erp-input w-24"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][inventory_variance_reason_code_id]" class="erp-select w-full min-w-[8rem]">
                                            <option value="">{{ __('None') }}</option>
                                            @foreach ($reasonCodes as $reasonCode)
                                                <option value="{{ $reasonCode->id }}" @selected(old('items.'.$index.'.inventory_variance_reason_code_id', $line->inventory_variance_reason_code_id) == $reasonCode->id)>
                                                    {{ $reasonCode->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="items[{{ $index }}][reason]"
                                            value="{{ old('items.'.$index.'.reason', $line->reason) }}"
                                            class="erp-input w-full min-w-[8rem]"
                                            placeholder="{{ __('Explanation') }}"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Save worksheet') }}</button>
                </div>
            </form>
        @else
            <div class="overflow-x-auto rounded-lg border border-erp-border">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Item') }}</th>
                            <th>{{ __('System') }}</th>
                            <th>{{ __('Counted') }}</th>
                            <th>{{ __('Variance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($count->items as $line)
                            <tr>
                                <td>{{ $line->inventoryItem?->item_name }}</td>
                                <td>{{ $line->system_quantity }}</td>
                                <td>{{ $line->counted_quantity ?? '—' }}</td>
                                <td>{{ $line->variance_quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex flex-wrap gap-2 border-t border-erp-border pt-3">
            @can('submit', $count)
                <form method="POST" action="{{ route('admin.inventory.stock-counts.submit', $count) }}">
                    @csrf
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Submit count') }}</button>
                </form>
            @endcan
            @can('approve', $count)
                <form method="POST" action="{{ route('admin.inventory.stock-counts.approve', $count) }}">
                    @csrf
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Approve') }}</button>
                </form>
            @endcan
            @can('post', $count)
                <form method="POST" action="{{ route('admin.inventory.stock-counts.post', $count) }}">
                    @csrf
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Post variance') }}</button>
                </form>
            @endcan
        </div>
    </div>
</x-admin.modal-form>
