@php
    use App\Support\Production\DigitalSpecificationService;

    $digitalForm = $digitalForm ?? DigitalSpecificationService::emptyForm(old('digital', []));
    $alpineDigital = $alpineDigital ?? null;
    $alpineQuantity = $alpineQuantity ?? null;
    $alpinePrice = $alpinePrice ?? null;
    $includeQuantity = $includeQuantity ?? true;
    $compact = $compact ?? false;
    $idPrefix = $idPrefix ?? 'digital';
    $customerName = $customerName ?? null;
    $inputClass = 'erp-input w-full min-h-[2.25rem] text-sm';
    $paperTypes = DigitalSpecificationService::paperTypes();
    $finishingOptions = DigitalSpecificationService::finishingOptions();
@endphp

<div
    class="space-y-4 rounded-lg border border-emerald-200 bg-emerald-50/40 p-3 sm:p-4"
    @unless ($alpineDigital)
        x-data="{
            digitalQty: @js((string) old('quantity', $quantityValue ?? '1')),
            digitalUps: @js($digitalForm['ups']),
            digitalSheets: @js($digitalForm['sheets']),
            digitalPrice: @js($digitalForm['price']),
            syncDigitalSheets() {
                const qty = Number(this.digitalQty) || 0;
                const ups = Number(this.digitalUps) || 0;
                if (qty > 0 && ups > 0) {
                    this.digitalSheets = String(Math.ceil(qty / ups));
                }
            },
            digitalAmountDisplay() {
                const qty = Number(this.digitalQty) || 0;
                const price = Number(this.digitalPrice) || 0;
                if (qty <= 0 || price <= 0) {
                    return '';
                }
                return (qty * price).toFixed(2);
            },
        }"
    @endunless
>
    @unless ($compact)
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-800">{{ __('Digital job') }}</h3>
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
                @if ($alpineDigital)
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
                name="digital[description]"
                class="{{ $inputClass }}"
                maxlength="500"
                required
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.description"
                @else
                    value="{{ $digitalForm['description'] }}"
                @endif
            >
            @error('digital.description')
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
                        @input="syncDigitalSheets()"
                    @else
                        value="{{ old('quantity', $quantityValue ?? '') }}"
                        x-model="digitalQty"
                        @input="syncDigitalSheets()"
                    @endif
                >
            </div>
        @endif
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-paper">{{ __('Paper type') }}</label>
            <input
                id="{{ $idPrefix }}-paper"
                type="text"
                name="digital[paper_type]"
                class="{{ $inputClass }}"
                list="{{ $idPrefix }}-paper-list"
                maxlength="80"
                required
                placeholder="{{ __('Adestor, Art 130…') }}"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.paper_type"
                @else
                    value="{{ $digitalForm['paper_type'] }}"
                @endif
            >
            <datalist id="{{ $idPrefix }}-paper-list">
                @foreach ($paperTypes as $paperType)
                    <option value="{{ $paperType }}"></option>
                @endforeach
            </datalist>
            @error('digital.paper_type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-ups">{{ __('No. of ups') }}</label>
            <input
                id="{{ $idPrefix }}-ups"
                type="number"
                name="digital[ups]"
                class="{{ $inputClass }}"
                min="1"
                max="9999"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.ups"
                    @input="syncDigitalSheets()"
                @else
                    value="{{ $digitalForm['ups'] }}"
                    x-model="digitalUps"
                    @input="syncDigitalSheets()"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-sheets">{{ __('No. of sheets') }}</label>
            <input
                id="{{ $idPrefix }}-sheets"
                type="number"
                name="digital[sheets]"
                class="{{ $inputClass }} border-amber-300 bg-amber-50"
                min="0"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.sheets"
                @else
                    value="{{ $digitalForm['sheets'] }}"
                    x-model="digitalSheets"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-finishing">{{ __('Finishing') }}</label>
            <input
                id="{{ $idPrefix }}-finishing"
                type="text"
                name="digital[finishing]"
                class="{{ $inputClass }}"
                list="{{ $idPrefix }}-finishing-list"
                maxlength="60"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.finishing"
                @else
                    value="{{ $digitalForm['finishing'] }}"
                @endif
            >
            <datalist id="{{ $idPrefix }}-finishing-list">
                @foreach ($finishingOptions as $finish)
                    <option value="{{ $finish }}"></option>
                @endforeach
            </datalist>
        </div>
        @unless ($compact)
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-price">{{ __('Price') }}</label>
            <input
                id="{{ $idPrefix }}-price"
                type="number"
                name="digital[price]"
                class="{{ $inputClass }}"
                min="0"
                step="0.01"
                @if ($alpineDigital)
                    x-model="{{ $alpinePrice ?? $alpineDigital.'.price' }}"
                @else
                    value="{{ $digitalForm['price'] }}"
                    x-model="digitalPrice"
                @endif
            >
        </div>
        <div>
            <label class="erp-label">{{ __('Amount') }}</label>
            <input
                type="text"
                class="{{ $inputClass }} bg-slate-50"
                readonly
                @if ($alpineDigital)
                    :value="digitalAmountDisplay()"
                @else
                    :value="digitalAmountDisplay()"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-due">{{ __('Due date') }}</label>
            <input
                id="{{ $idPrefix }}-due"
                type="date"
                name="digital[due_date]"
                class="{{ $inputClass }}"
                min="{{ now()->toDateString() }}"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.due_date"
                @else
                    value="{{ $digitalForm['due_date'] }}"
                @endif
            >
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-payment">{{ __('Payment status') }}</label>
            <select
                id="{{ $idPrefix }}-payment"
                name="digital[payment_status]"
                class="{{ $inputClass }}"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.payment_status"
                @endif
            >
                <option value="">{{ __('Select…') }}</option>
                @foreach (DigitalSpecificationService::paymentStatuses() as $value => $label)
                    <option value="{{ $value }}" @selected(! $alpineDigital && $digitalForm['payment_status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label" for="{{ $idPrefix }}-status">{{ __('Status') }}</label>
            <select
                id="{{ $idPrefix }}-status"
                name="digital[status]"
                class="{{ $inputClass }}"
                @if ($alpineDigital)
                    x-model="{{ $alpineDigital }}.status"
                @endif
            >
                <option value="">{{ __('Select…') }}</option>
                @foreach (DigitalSpecificationService::jobStatuses() as $value => $label)
                    <option value="{{ $value }}" @selected(! $alpineDigital && $digitalForm['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endunless
    </div>
</div>
