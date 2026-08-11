<x-admin.modal-form :title="__('Material shortages')" maxWidth="3xl">
    <div class="space-y-4">
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-medium">{{ __('This job cannot enter the production queue until required stock is available.') }}</p>
            <p class="mt-1">{{ __('Order :order · Job :job', [
                'order' => $salesOrder->order_number,
                'job' => $jobCard->job_card_number,
            ]) }}</p>
        </div>

        @if (! ($materials['ready'] ?? false) && ! empty($materials['missing']))
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
        @else
            <p class="text-sm text-slate-600">{{ $materials['detail'] ?? __('Material readiness could not be assessed.') }}</p>
        @endif

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <p class="font-medium text-slate-900">{{ __('Who resolves this?') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @if ($canReceiveStock)
                    <li>{{ __('Receive the missing items into warehouse stock (Inventory → Stock receipts).') }}</li>
                @else
                    <li>{{ __('Ask the storekeeper to receive the missing items into warehouse stock.') }}</li>
                @endif
                @if ($canReserveMaterials)
                    <li>{{ __('Open the job card Materials tab and reserve stock against this job.') }}</li>
                @else
                    <li>{{ __('Ask production or inventory staff to reserve stock on the job card Materials tab.') }}</li>
                @endif
                <li>{{ __('Return here and submit to the production queue once Materials shows ready.') }}</li>
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
            @if ($canReceiveStock)
                <a
                    href="{{ route('admin.inventory.receipts.index') }}"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                >{{ __('Go to stock receipts') }}</a>
            @endif
            <button type="button" class="erp-btn-secondary text-sm" data-erp-form-modal-close>{{ __('Close') }}</button>
        </div>
    </div>
</x-admin.modal-form>
