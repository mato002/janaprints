<x-admin.card class="mb-6">
    <h2 class="mb-4 font-medium text-slate-900">{{ __('Line items') }}</h2>

    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left">{{ __('Item') }}</th>
                    <th class="text-right">{{ __('Qty') }}</th>
                    <th class="text-right">{{ __('Unit price') }}</th>
                    <th class="text-right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotation->items as $item)
                    <tr>
                        <td>
                            <span class="font-medium text-slate-900">{{ $item->item_name }}</span>
                            @if ($item->description)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format((float) $item->quantity, 0) }}</td>
                        <td class="text-right">{{ $quotation->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-right font-medium">{{ $quotation->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-500">{{ __('No line items yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <dl class="mt-6 ml-auto w-full max-w-xs space-y-2 border-t border-erp-border pt-4 text-sm">
        <div class="flex items-center justify-between">
            <dt class="text-slate-600">{{ __('Subtotal') }}</dt>
            <dd>{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-slate-600">{{ __('Tax') }}</dt>
            <dd>{{ $quotation->currency }} {{ number_format((float) $quotation->tax_amount, 2) }}</dd>
        </div>
        @if ((float) $quotation->discount_amount > 0)
            <div class="flex items-center justify-between">
                <dt class="text-slate-600">{{ __('Discount') }}</dt>
                <dd>{{ $quotation->currency }} {{ number_format((float) $quotation->discount_amount, 2) }}</dd>
            </div>
        @endif
        <div class="flex items-center justify-between border-t border-erp-border pt-2 font-semibold text-slate-900">
            <dt>{{ __('Total') }}</dt>
            <dd>{{ $quotation->currency }} {{ number_format((float) $quotation->total_amount, 2) }}</dd>
        </div>
    </dl>
</x-admin.card>
