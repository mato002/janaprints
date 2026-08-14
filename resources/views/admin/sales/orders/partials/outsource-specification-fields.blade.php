@php
    use App\Enums\ProductionType;
    use App\Support\Production\OutsourceSpecificationService;

    $outsourceForm = $outsourceForm ?? OutsourceSpecificationService::emptyForm(old('outsource', []));
    $alpineOutsource = $alpineOutsource ?? null;
    $alpineQuantity = $alpineQuantity ?? null;
    $includeQuantity = $includeQuantity ?? true;
    $idPrefix = $idPrefix ?? 'outsource';
    $productionVendors = $productionVendors ?? collect();
    $customerName = $customerName ?? null;
    $compact = $compact ?? false;
    $inputClass = 'erp-input w-full min-h-[2.25rem] text-sm';
@endphp

<div class="space-y-4 rounded-lg border border-violet-200 bg-violet-50/40 p-3 sm:p-4">
    @unless ($compact)
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-violet-800">{{ __('Outsourced job') }}</h3>
        </div>
    @endunless

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @unless ($compact)
            <div>
                <label class="erp-label">{{ __('Date') }}</label>
                <input type="text" class="{{ $inputClass }} bg-slate-50" value="{{ now()->format('d/m/Y') }}" readonly>
            </div>
            <div>
                <label class="erp-label">{{ __('Client name') }}</label>
                @if ($alpineOutsource)
                    <input type="text" class="{{ $inputClass }} bg-slate-50" :value="context?.customer_name ?? @js($customerName)" readonly>
                @else
                    <input type="text" class="{{ $inputClass }} bg-slate-50" value="{{ $customerName }}" readonly>
                @endif
            </div>
        @endunless
        <div class="sm:col-span-2 lg:col-span-1">
            <label class="erp-label" for="{{ $idPrefix }}-description">{{ __('Description') }}</label>
            <input
                id="{{ $idPrefix }}-description"
                type="text"
                name="outsource[description]"
                class="{{ $inputClass }}"
                maxlength="500"
                required
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.description"
                @else
                    value="{{ $outsourceForm['description'] }}"
                @endif
            >
            @error('outsource.description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @if ($includeQuantity)
            <div>
                <label class="erp-label" for="{{ $idPrefix }}-quantity">{{ __('Quantity') }}</label>
                <input
                    id="{{ $idPrefix }}-quantity"
                    type="number"
                    name="quantity"
                    class="{{ $inputClass }}"
                    min="0.001"
                    step="any"
                    required
                    @if ($alpineQuantity)
                        x-model="{{ $alpineQuantity }}"
                    @else
                        value="{{ old('quantity', $quantityValue ?? '') }}"
                    @endif
                >
            </div>
        @endif
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-printing-type">{{ __('Type of printing') }}</label>
            <select
                id="{{ $idPrefix }}-printing-type"
                name="outsource[printing_type]"
                class="{{ $inputClass }}"
                required
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.printing_type"
                @endif
            >
                <option value="">{{ __('Select…') }}</option>
                @foreach (ProductionType::cases() as $type)
                    <option
                        value="{{ $type->value }}"
                        @selected(! $alpineOutsource && $outsourceForm['printing_type'] === $type->value)
                    >{{ str_replace('_', ' ', ucfirst($type->value)) }}</option>
                @endforeach
            </select>
            @error('outsource.printing_type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-vendor">{{ __('Service provider') }}</label>
            <select
                id="{{ $idPrefix }}-vendor"
                name="outsource[vendor_id]"
                class="{{ $inputClass }}"
                required
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.vendor_id"
                @endif
            >
                <option value="">{{ __('Select vendor…') }}</option>
                @foreach ($productionVendors as $vendor)
                    <option
                        value="{{ $vendor->id }}"
                        @selected(! $alpineOutsource && (string) $outsourceForm['vendor_id'] === (string) $vendor->id)
                    >{{ $vendor->vendor_name }}</option>
                @endforeach
            </select>
            @error('outsource.vendor_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @if ($productionVendors->isEmpty())
                <p class="mt-1 text-xs text-amber-700">{{ __('No production vendors yet. Mark a vendor as a production vendor in Procurement.') }}</p>
            @endif
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-cost">{{ __('Cost') }}</label>
            <input
                id="{{ $idPrefix }}-cost"
                type="number"
                name="outsource[cost]"
                class="{{ $inputClass }}"
                min="0"
                step="0.01"
                required
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.cost"
                @else
                    value="{{ $outsourceForm['cost'] }}"
                @endif
            >
            @error('outsource.cost')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @unless ($compact)
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-selling">{{ __('Selling price') }}</label>
            <input
                id="{{ $idPrefix }}-selling"
                type="number"
                name="outsource[selling_price]"
                class="{{ $inputClass }}"
                min="0"
                step="0.01"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.selling_price"
                    @input="syncOutsourceSellingPrice()"
                @else
                    value="{{ $outsourceForm['selling_price'] }}"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-payment">{{ __('Payment status') }}</label>
            <select
                id="{{ $idPrefix }}-payment"
                name="outsource[payment_status]"
                class="{{ $inputClass }}"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.payment_status"
                @endif
            >
                <option value="">{{ __('Select…') }}</option>
                @foreach (OutsourceSpecificationService::paymentStatuses() as $value => $label)
                    <option value="{{ $value }}" @selected(! $alpineOutsource && $outsourceForm['payment_status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-status">{{ __('Status') }}</label>
            <select
                id="{{ $idPrefix }}-status"
                name="outsource[status]"
                class="{{ $inputClass }}"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.status"
                @endif
            >
                <option value="">{{ __('Select…') }}</option>
                @foreach (OutsourceSpecificationService::jobStatuses() as $value => $label)
                    <option value="{{ $value }}" @selected(! $alpineOutsource && $outsourceForm['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-sent">{{ __('Date sent out') }}</label>
            <input
                id="{{ $idPrefix }}-sent"
                type="date"
                name="outsource[date_sent_out]"
                class="{{ $inputClass }}"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.date_sent_out"
                @else
                    value="{{ $outsourceForm['date_sent_out'] }}"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-due">{{ __('Due date / time') }}</label>
            <input
                id="{{ $idPrefix }}-due"
                type="datetime-local"
                name="outsource[due_at]"
                class="{{ $inputClass }}"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.due_at"
                @else
                    value="{{ $outsourceForm['due_at'] }}"
                @endif
            >
        </div>
        @endunless
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="erp-label" for="{{ $idPrefix }}-notes">{{ __('Notes') }}</label>
            <textarea
                id="{{ $idPrefix }}-notes"
                name="outsource[notes]"
                class="erp-input w-full"
                rows="2"
                @if ($alpineOutsource)
                    x-model="{{ $alpineOutsource }}.notes"
                @endif
            >@unless($alpineOutsource){{ $outsourceForm['notes'] }}@endunless</textarea>
        </div>
    </div>
</div>
