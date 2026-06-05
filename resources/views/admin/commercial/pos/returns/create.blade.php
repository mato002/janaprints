<x-admin-layout :title="__('Create Return')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Returns'), 'url' => route('admin.commercial.pos.returns.dashboard')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Create Return')" :description="__('Locate a paid sale, select items, and submit for approval.')" />

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-admin.card class="mb-6">
        <h3 class="mb-3 font-medium">{{ __('Step 1: Locate Sale') }}</h3>
        <form method="GET" action="{{ route('admin.commercial.pos.returns.create') }}" class="flex flex-wrap gap-3">
            <input type="text" name="sale" value="{{ $search }}" placeholder="{{ __('Sale number e.g. POS-20260604-0001') }}" class="erp-input min-w-[16rem]">
            <button type="submit" class="erp-btn-secondary">{{ __('Find Sale') }}</button>
        </form>
    </x-admin.card>

    @if ($sale)
        <form method="POST" action="{{ route('admin.commercial.pos.returns.store') }}">
            @csrf
            <input type="hidden" name="sale_number" value="{{ $sale->sale_number }}">

            <div class="mb-6 grid gap-6 lg:grid-cols-2">
                <x-admin.card>
                    <h3 class="mb-3 font-medium">{{ __('Original Sale') }}</h3>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-slate-500">{{ __('Sale #') }}</dt><dd class="font-medium">{{ $sale->sale_number }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('Date') }}</dt><dd>{{ $sale->sale_date->format('Y-m-d') }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd>{{ $sale->cashier?->name }}</dd></div>
                        <div><dt class="text-slate-500">{{ __('Total') }}</dt><dd class="tabular-nums font-semibold">{{ number_format($sale->total_amount, 2) }}</dd></div>
                    </dl>
                </x-admin.card>

                <x-admin.card>
                    <h3 class="mb-3 font-medium">{{ __('Return Details') }}</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="mb-1 block text-slate-500">{{ __('Return Type') }}</label>
                            <select name="return_type" class="erp-input w-full" required>
                                @foreach ($returnTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-slate-500">{{ __('Refund Method') }}</label>
                            <select name="refund_method" class="erp-input w-full" required>
                                @foreach ($refundMethods as $method)
                                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-slate-500">{{ __('Reason') }}</label>
                            <textarea name="reason" rows="3" class="erp-input w-full" required>{{ old('reason') }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-slate-500">{{ __('Notes') }}</label>
                            <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <x-admin.card class="mb-6">
                <h3 class="mb-3 font-medium">{{ __('Step 2: Select Items') }}</h3>
                @if ($returnableItems->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('No returnable items remain on this sale.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-erp-border text-left text-slate-500">
                                    <th class="py-2 pr-4">{{ __('Item') }}</th>
                                    <th class="py-2 pr-4">{{ __('Sold') }}</th>
                                    <th class="py-2 pr-4">{{ __('Returnable') }}</th>
                                    <th class="py-2 pr-4">{{ __('Return Qty') }}</th>
                                    <th class="py-2">{{ __('Line Reason') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returnableItems as $index => $row)
                                    @php($item = $row['item'])
                                    <tr class="border-b border-erp-border/60">
                                        <td class="py-2 pr-4">{{ $item->description }}</td>
                                        <td class="py-2 pr-4 tabular-nums">{{ $item->quantity }}</td>
                                        <td class="py-2 pr-4 tabular-nums">{{ $row['returnable_qty'] }}</td>
                                        <td class="py-2 pr-4">
                                            <input type="hidden" name="lines[{{ $index }}][pos_sale_item_id]" value="{{ $item->id }}">
                                            <input type="number" name="lines[{{ $index }}][quantity_returned]" min="0" max="{{ $row['returnable_qty'] }}" step="0.001" value="0" class="erp-input w-28">
                                        </td>
                                        <td class="py-2">
                                            <input type="text" name="lines[{{ $index }}][reason]" class="erp-input w-full" placeholder="{{ __('Optional') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">{{ __('For full returns, set return type to Full Return and enter quantities for all items, or leave quantities at returnable amounts.') }}</p>
                @endif
            </x-admin.card>

            @if ($returnableItems->isNotEmpty())
                <div class="flex justify-end">
                    <button type="submit" class="erp-btn-primary">{{ __('Submit Return for Approval') }}</button>
                </div>
            @endif
        </form>
    @elseif ($search)
        <x-admin.card>
            <p class="text-sm text-slate-500">{{ __('No returnable paid sale found for :search.', ['search' => $search]) }}</p>
        </x-admin.card>
    @endif
</x-admin-layout>
