@php
    use App\Support\Production\OffsetJobSheetService;

    $jobSheetForm = $jobSheetForm ?? OffsetJobSheetService::emptyForm(old('job_sheet', []));
    $alpineJobSheet = $alpineJobSheet ?? null;
    $alpineQuantity = $alpineQuantity ?? null;
    $alpineCollectionDate = $alpineCollectionDate ?? null;
    $includeQuantity = $includeQuantity ?? true;
    $includeCollectionDate = $includeCollectionDate ?? true;
    $compact = $compact ?? false;
    $idPrefix = $idPrefix ?? 'job-sheet';
    $inputClass = 'erp-input w-full min-h-[2.25rem] text-sm';
@endphp

<div class="space-y-4 rounded-lg border border-erp-accent/30 bg-erp-accent/[0.03] p-3 sm:p-4">
    @unless ($compact)
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-accent">{{ __('Offset job sheet') }}</h3>
        </div>
    @endunless

    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-erp-accent">{{ __('Printing specifications') }}</p>
        <div class="overflow-x-auto rounded-md border border-erp-border bg-white">
            <table class="erp-table w-full min-w-[44rem] text-sm">
                <thead>
                    <tr>
                        @if ($includeQuantity)
                            <th class="w-[7rem]">{{ __('Qty') }}</th>
                        @endif
                        <th>{{ __('Description') }}</th>
                        <th class="w-[16rem]">{{ __('Paper colour') }}</th>
                        <th>{{ __('Paper stock') }}</th>
                        <th class="w-[8rem]">{{ __('Ink') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if ($includeQuantity)
                            <td>
                                <label class="sr-only" for="{{ $idPrefix }}-quantity">{{ __('Quantity') }}</label>
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
                            </td>
                        @endif
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-description">{{ __('Description') }}</label>
                            <textarea
                                id="{{ $idPrefix }}-description"
                                name="job_sheet[product_description]"
                                class="{{ $inputClass }}"
                                rows="2"
                                required
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.product_description"
                                @endif
                            >@unless($alpineJobSheet){{ $jobSheetForm['product_description'] }}@endunless</textarea>
                        </td>
                        <td>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach ([
                                    'paper_colour_orig' => __('ORIG'),
                                    'paper_colour_dup' => __('DUP'),
                                    'paper_colour_tri' => __('TRI'),
                                    'paper_colour_quad' => __('QUAD'),
                                ] as $colourKey => $colourLabel)
                                    <label class="block">
                                        <span class="mb-0.5 block text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">{{ $colourLabel }}</span>
                                        <input
                                            type="text"
                                            name="job_sheet[{{ $colourKey }}]"
                                            class="{{ $inputClass }}"
                                            maxlength="40"
                                            @if ($alpineJobSheet)
                                                x-model="{{ $alpineJobSheet }}.{{ $colourKey }}"
                                            @else
                                                value="{{ $jobSheetForm[$colourKey] }}"
                                            @endif
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-paper-stock">{{ __('Paper stock') }}</label>
                            <input
                                id="{{ $idPrefix }}-paper-stock"
                                type="text"
                                name="job_sheet[paper_stock]"
                                class="{{ $inputClass }}"
                                maxlength="120"
                                required
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.paper_stock"
                                @else
                                    value="{{ $jobSheetForm['paper_stock'] }}"
                                @endif
                            >
                            @error('job_sheet.paper_stock')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-ink">{{ __('Ink') }}</label>
                            <input
                                id="{{ $idPrefix }}-ink"
                                type="text"
                                name="job_sheet[ink]"
                                class="{{ $inputClass }}"
                                maxlength="80"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.ink"
                                @else
                                    value="{{ $jobSheetForm['ink'] }}"
                                @endif
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @error('job_sheet.product_description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('job_sheet.size')
            {{-- shown under binding --}}
        @enderror
    </div>

    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-erp-accent">{{ __('Binding specifications') }}</p>
        <div class="overflow-x-auto rounded-md border border-erp-border bg-white">
            <table class="erp-table w-full min-w-[44rem] text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Pages / pad') }}</th>
                        <th>{{ __('Size') }}</th>
                        <th>{{ __('No. of ups') }}</th>
                        <th>{{ __('Binding') }}</th>
                        @if ($includeCollectionDate)
                            <th>{{ __('Date of collection') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-serial">{{ __('Number') }}</label>
                            <input
                                id="{{ $idPrefix }}-serial"
                                type="text"
                                name="job_sheet[serial_number]"
                                class="{{ $inputClass }}"
                                maxlength="80"
                                placeholder="{{ __('Serial start') }}"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.serial_number"
                                @else
                                    value="{{ $jobSheetForm['serial_number'] }}"
                                @endif
                            >
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-pages">{{ __('Pages / pad') }}</label>
                            <input
                                id="{{ $idPrefix }}-pages"
                                type="text"
                                name="job_sheet[pages_per_pad]"
                                class="{{ $inputClass }}"
                                maxlength="40"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.pages_per_pad"
                                @else
                                    value="{{ $jobSheetForm['pages_per_pad'] }}"
                                @endif
                            >
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-size">{{ __('Size') }}</label>
                            <input
                                id="{{ $idPrefix }}-size"
                                type="text"
                                name="job_sheet[size]"
                                class="{{ $inputClass }}"
                                maxlength="80"
                                required
                                placeholder="A5"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.size"
                                @else
                                    value="{{ $jobSheetForm['size'] }}"
                                @endif
                            >
                            @error('job_sheet.size')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-ups">{{ __('No. of ups') }}</label>
                            <input
                                id="{{ $idPrefix }}-ups"
                                type="number"
                                name="job_sheet[ups]"
                                class="{{ $inputClass }}"
                                min="1"
                                max="9999"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.ups"
                                @else
                                    value="{{ $jobSheetForm['ups'] }}"
                                @endif
                            >
                        </td>
                        <td>
                            <label class="sr-only" for="{{ $idPrefix }}-binding">{{ __('Binding') }}</label>
                            <input
                                id="{{ $idPrefix }}-binding"
                                type="text"
                                name="job_sheet[binding_type]"
                                class="{{ $inputClass }}"
                                maxlength="60"
                                placeholder="{{ __('Pad / staple / none') }}"
                                @if ($alpineJobSheet)
                                    x-model="{{ $alpineJobSheet }}.binding_type"
                                @else
                                    value="{{ $jobSheetForm['binding_type'] }}"
                                @endif
                            >
                        </td>
                        @if ($includeCollectionDate)
                            <td>
                                <label class="sr-only" for="{{ $idPrefix }}-collection">{{ __('Date of collection') }}</label>
                                <input
                                    id="{{ $idPrefix }}-collection"
                                    type="date"
                                    name="required_date"
                                    class="{{ $inputClass }}"
                                    min="{{ now()->toDateString() }}"
                                    @if ($alpineCollectionDate)
                                        x-model="{{ $alpineCollectionDate }}"
                                    @else
                                        value="{{ old('required_date') }}"
                                    @endif
                                >
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <label class="erp-label" for="{{ $idPrefix }}-notes">{{ __('Note') }}</label>
        <textarea
            id="{{ $idPrefix }}-notes"
            name="job_sheet[production_notes]"
            class="erp-input w-full"
            rows="2"
            @if ($alpineJobSheet)
                x-model="{{ $alpineJobSheet }}.production_notes"
            @endif
        >@unless($alpineJobSheet){{ $jobSheetForm['production_notes'] }}@endunless</textarea>
    </div>

    <div
        x-data="{
            rows: @js($jobSheetForm['material_rows']),
            addRow() {
                if (this.rows.length >= 10) {
                    return;
                }
                this.rows.push({ paper_type: '', sheets_a4_a3: '', sheets_a1: '' });
            },
            removeRow(index) {
                if (this.rows.length <= 1) {
                    this.rows[0] = { paper_type: '', sheets_a4_a3: '', sheets_a1: '' };
                    return;
                }
                this.rows.splice(index, 1);
            },
        }"
    >
        <div class="mb-2 flex items-center justify-between gap-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-erp-accent">{{ __('Material requisition') }}</p>
            <button type="button" class="erp-btn-secondary text-xs" @click="addRow()" :disabled="rows.length >= 10">{{ __('Add paper') }}</button>
        </div>
        <div class="overflow-x-auto rounded-md border border-erp-border bg-white">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Paper type') }}</th>
                        <th class="w-[10rem]">{{ __('No. of sheets A4 / A3') }}</th>
                        <th class="w-[10rem]">{{ __('No. of sheets A1') }}</th>
                        <th class="w-[3rem]"><span class="sr-only">{{ __('Remove') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="index">
                        <tr>
                            <td>
                                <input
                                    type="text"
                                    class="{{ $inputClass }}"
                                    maxlength="120"
                                    :name="'job_sheet[material_rows][' + index + '][paper_type]'"
                                    x-model="row.paper_type"
                                >
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="{{ $inputClass }}"
                                    maxlength="40"
                                    :name="'job_sheet[material_rows][' + index + '][sheets_a4_a3]'"
                                    x-model="row.sheets_a4_a3"
                                >
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="{{ $inputClass }}"
                                    maxlength="40"
                                    :name="'job_sheet[material_rows][' + index + '][sheets_a1]'"
                                    x-model="row.sheets_a1"
                                >
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="erp-btn-ghost text-xs text-slate-500"
                                    @click="removeRow(index)"
                                    :disabled="rows.length <= 1"
                                >{{ __('Remove') }}</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
