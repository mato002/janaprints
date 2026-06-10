@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')],
        ['label' => $count->count_number, 'url' => route('admin.inventory.stock-counts.show', $count)],
        ['label' => __('Worksheet')],
    ];
    $editable = auth()->user()->can('update', $count);
@endphp
<x-admin-layout :title="__('Worksheet').' — '.$count->count_number" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Count worksheet')">
        <x-admin.enum-status-badge :status="$count->status->value" />
        <x-admin.export-dropdown
            export-route="admin.inventory.stock-counts.export"
            :export-route-params="['stockCount' => $count]"
            :format-in-path="true"
        />
    </x-admin.page-header>

    <x-admin.card>
        <p class="text-sm text-slate-600 mb-4">{{ $count->warehouse?->name }} · {{ $count->count_date->format('Y-m-d') }}</p>

        @if ($editable)
            <form method="POST" action="{{ route('admin.inventory.stock-counts.worksheet.update', $count) }}">
                @csrf @method('PUT')
                <x-admin.data-table :searchable="true" export-filename="worksheet">
                    <x-slot name="head">
                        <tr>
                            <th>{{ __('Item') }}</th>
                            <th>{{ __('System qty') }}</th>
                            <th>{{ __('Counted qty') }}</th>
                            <th>{{ __('Variance') }}</th>
                            <th>{{ __('Reason code') }}</th>
                            <th>{{ __('Comment') }}</th>
                        </tr>
                    </x-slot>
                    <x-slot name="body">
                        @foreach ($count->items as $index => $line)
                            @php($variance = (float) old('items.'.$index.'.counted_quantity', $line->counted_quantity ?? $line->system_quantity) - (float) $line->system_quantity)
                            <tr>
                                <td>{{ $line->inventoryItem?->item_name }}<input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $line->inventory_item_id }}"></td>
                                <td>{{ $line->system_quantity }}</td>
                                <td><input type="number" step="0.001" min="0" name="items[{{ $index }}][counted_quantity]" value="{{ old('items.'.$index.'.counted_quantity', $line->counted_quantity) }}" class="erp-input w-28" required></td>
                                <td class="tabular-nums text-sm {{ abs($variance) >= 0.001 ? 'text-amber-700 font-medium' : 'text-slate-500' }}">{{ number_format($variance, 3) }}</td>
                                <td>
                                    <select name="items[{{ $index }}][inventory_variance_reason_code_id]" class="erp-select w-full min-w-[10rem]">
                                        <option value="">{{ __('Legacy text / none') }}</option>
                                        @foreach ($reasonCodes as $reasonCode)
                                            <option value="{{ $reasonCode->id }}" @selected(old('items.'.$index.'.inventory_variance_reason_code_id', $line->inventory_variance_reason_code_id) == $reasonCode->id)>{{ $reasonCode->code }} — {{ $reasonCode->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="items[{{ $index }}][reason]" value="{{ old('items.'.$index.'.reason', $line->reason) }}" class="erp-input w-full" placeholder="{{ __('Explanation or comment') }}"></td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-admin.data-table>
                <p class="mt-2 text-xs text-slate-500">{{ __('Lines with variance require a reason code or legacy explanation before approval.') }}</p>
                <div class="mt-4"><button type="submit" class="erp-btn-primary">{{ __('Save worksheet') }}</button></div>
            </form>
        @else
            <x-admin.data-table :searchable="true" export-filename="worksheet">
                <x-slot name="head">
                    <tr><th>{{ __('Item') }}</th><th>{{ __('System') }}</th><th>{{ __('Counted') }}</th><th>{{ __('Variance') }}</th><th>{{ __('Reason code') }}</th><th>{{ __('Comment') }}</th></tr>
                </x-slot>
                <x-slot name="body">
                    @foreach ($count->items as $line)
                        <tr>
                            <td>{{ $line->inventoryItem?->item_name }}</td>
                            <td>{{ $line->system_quantity }}</td>
                            <td>{{ $line->counted_quantity ?? '—' }}</td>
                            <td>{{ $line->variance_quantity }}</td>
                            <td>{{ $line->varianceReasonCode?->name ?? '—' }}</td>
                            <td>{{ $line->reason ?: $line->notes }}</td>
                        </tr>
                    @endforeach
                </x-slot>
            </x-admin.data-table>
        @endif
    </x-admin.card>
</x-admin-layout>
