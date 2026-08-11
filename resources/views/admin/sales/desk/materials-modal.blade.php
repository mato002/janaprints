<x-admin.modal-form :title="__('Production materials')" maxWidth="3xl">
    <div class="space-y-4">
        <div @class([
            'rounded-lg border px-4 py-3 text-sm',
            'border-rose-200 bg-rose-50 text-rose-900' => $issueType !== 'ready',
            'border-emerald-200 bg-emerald-50 text-emerald-900' => $issueType === 'ready',
        ])>
            <p class="font-medium">{{ __('Order :order', ['order' => $salesOrder->order_number]) }}</p>
            @if ($productName)
                <p class="mt-1">{{ __('Product: :product', ['product' => $productName]) }}</p>
            @endif
            @if ($jobCard)
                <p class="mt-1">{{ __('Job: :job', ['job' => $jobCard->job_card_number]) }}</p>
            @endif
        </div>

        @if ($issueType === 'bom')
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-medium">{{ __('No bill of materials configured') }}</p>
                <p class="mt-2">{{ __('This product does not have a BOM yet, so the system cannot calculate raw materials or check stock. Production must configure the BOM before this order can be released.') }}</p>
            </div>
        @elseif ($issueType === 'no_product')
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-medium">{{ __('No inventory product linked') }}</p>
                <p class="mt-2">{{ __('Link a catalogue product to this order so material requirements can be generated.') }}</p>
            </div>
        @elseif ($issueType === 'shortage' && ! empty($materials['missing']))
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Short materials') }}</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($materials['missing'] as $line)
                        @php
                            $qty = rtrim(rtrim(number_format($line['shortfall'], 3, '.', ''), '0'), '.');
                            $unit = $line['unit'] ? ' '.$line['unit'] : '';
                        @endphp
                        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                            <span class="font-medium text-slate-900">{{ $line['item'] }}</span>
                            <span class="text-slate-600"> — {{ __('Need :qty:unit more in stock', ['qty' => $qty, 'unit' => $unit]) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif ($issueType === 'requirements')
            <p class="text-sm text-slate-700">{{ __('Material requirements have not been generated for this job yet.') }}</p>
        @else
            <p class="text-sm text-slate-700">{{ $materials['detail'] ?? __('Material readiness could not be assessed.') }}</p>
        @endif

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <p class="font-medium text-slate-900">{{ __('What happens next') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @if ($issueType === 'bom')
                    @if ($canManageBom)
                        <li>{{ __('Create or activate a BOM for this finished product in Production.') }}</li>
                    @else
                        <li>{{ __('Ask production to create a BOM for this product.') }}</li>
                    @endif
                    @if ($canEditOrder)
                        <li>{{ __('Or edit the order if the wrong product was selected.') }}</li>
                    @endif
                @elseif ($issueType === 'no_product')
                    @if ($canEditOrder)
                        <li>{{ __('Edit the order and link the correct catalogue product.') }}</li>
                    @else
                        <li>{{ __('Ask sales support to link the correct catalogue product on the order.') }}</li>
                    @endif
                @else
                    @if ($canReceiveStock)
                        <li>{{ __('Receive missing stock through Inventory → Stock receipts.') }}</li>
                    @else
                        <li>{{ __('Ask the storekeeper to receive the missing stock.') }}</li>
                    @endif
                    @if ($canReserveMaterials)
                        <li>{{ __('Reserve stock on the job card Materials tab once stock is available.') }}</li>
                    @else
                        <li>{{ __('Ask production or inventory staff to reserve stock on the job card.') }}</li>
                    @endif
                @endif
                <li>{{ __('Use Save & continue later on the sales desk — this order stays in Needs attention until you return.') }}</li>
            </ul>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($canOpenJobCard && ($materials['materials_url'] ?? null))
                <a
                    href="{{ $materials['materials_url'] }}"
                    class="erp-btn-primary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                >{{ __('Open job card materials') }}</a>
            @endif
            @if ($canEditOrder)
                <a
                    href="{{ route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk']) }}"
                    class="erp-btn-secondary text-sm"
                    data-erp-modal-open
                >{{ __('Edit order') }}</a>
            @endif
            @if ($canManageBom)
                <a
                    href="{{ route('admin.production.boms.index') }}"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                >{{ __('Manage BOMs') }}</a>
            @endif
            @if ($canReceiveStock)
                <a
                    href="{{ route('admin.inventory.receipts.index') }}"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                >{{ __('Stock receipts') }}</a>
            @endif
            <a
                href="{{ $resumeUrl }}"
                class="erp-btn-secondary text-sm"
                data-turbo-frame="erp-main"
                data-erp-form-modal-close
            >{{ __('Back to walk-in') }}</a>
            <button type="button" class="erp-btn-secondary text-sm" data-erp-form-modal-close>{{ __('Close') }}</button>
        </div>
    </div>
</x-admin.modal-form>
