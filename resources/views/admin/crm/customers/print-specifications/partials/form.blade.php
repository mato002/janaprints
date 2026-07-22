@props([
    'customer',
    'specification' => null,
    'serialProfile' => null,
    'serialSummary' => null,
    'statuses' => [],
    'billingTypes' => [],
    'fulfilmentMethods' => [],
    'artworkTypes' => [],
    'showArtworkUpload' => true,
    'defaultStatus' => 'draft',
])

@php
    $spec = $specification;
    $item = $spec?->inventoryItem;
    $statusValue = old('status', $spec?->status?->value ?? $defaultStatus);
@endphp

<div class="space-y-6">
    @if (! empty($liveReferenceWarnings))
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            @foreach ($liveReferenceWarnings as $warning)
                <p>{{ $warning }}</p>
            @endforeach
        </div>
    @endif

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Specification Details') }}</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" class="erp-input w-full" required maxlength="255"
                    value="{{ old('name', $spec?->name) }}" placeholder="{{ __('e.g. Fortress Receipt Book') }}">
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="description">{{ __('Description') }}</label>
                <textarea id="description" name="description" class="erp-input w-full" rows="2">{{ old('description', $spec?->description) }}</textarea>
            </div>
            <div>
                <label class="erp-label" for="status">{{ __('Status') }}</label>
                @if ($spec?->isReadOnly())
                    <input class="erp-input w-full bg-slate-50" readonly value="{{ $spec->status->label() }}">
                @else
                    <select id="status" name="status" class="erp-input w-full" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($statusValue === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            @if ($spec)
                <div>
                    <label class="erp-label">{{ __('Specification code') }}</label>
                    <input class="erp-input w-full bg-slate-50" readonly value="{{ $spec->specification_code }}">
                </div>
            @endif
            <div>
                <label class="erp-label" for="default_quantity">{{ __('Default quantity') }}</label>
                <input type="number" step="0.001" min="0" id="default_quantity" name="default_quantity" class="erp-input w-full"
                    value="{{ old('default_quantity', $spec?->default_quantity) }}">
            </div>
            <div>
                <label class="erp-label" for="default_unit_price">{{ __('Default unit price') }}</label>
                <input type="number" step="0.01" min="0" id="default_unit_price" name="default_unit_price" class="erp-input w-full"
                    value="{{ old('default_unit_price', $spec?->default_unit_price) }}">
            </div>
            <div>
                <label class="erp-label" for="default_billing_type">{{ __('Default billing type') }}</label>
                <select id="default_billing_type" name="default_billing_type" class="erp-input w-full">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($billingTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('default_billing_type', $spec?->default_billing_type?->value) === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="default_fulfilment_method">{{ __('Default fulfilment') }}</label>
                <select id="default_fulfilment_method" name="default_fulfilment_method" class="erp-input w-full">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($fulfilmentMethods as $method)
                        <option value="{{ $method->value }}" @selected(old('default_fulfilment_method', $spec?->default_fulfilment_method?->value) === $method->value)>
                            {{ $method->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="customer_instructions">{{ __('Customer instructions') }}</label>
                <textarea id="customer_instructions" name="customer_instructions" class="erp-input w-full" rows="2">{{ old('customer_instructions', $spec?->customer_instructions) }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Product / Inventory Item') }}</h3>
        <x-admin.lookup-select
            name="inventory_item_id"
            :label="__('Product / Inventory Item')"
            :options="collect(old('inventory_item_id', $spec?->inventory_item_id) ? [[
                'value' => old('inventory_item_id', $spec?->inventory_item_id),
                'label' => $item?->item_name ? $item->item_name.' ('.$item->sku.')' : __('Selected product'),
            ]] : [])"
            :value="old('inventory_item_id', $spec?->inventory_item_id)"
            :required="true"
            refresh-route="admin.lookups.items"
            select-class="erp-input w-full"
            :empty-option="false"
        />
        <p class="mt-2 text-xs text-slate-500">{{ __('Manufacturing defaults (BOM, route, QC, serial capability) come from the catalogue product.') }}</p>
    </section>

    @if ($showArtworkUpload)
        <section class="rounded-lg border border-erp-border p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Artwork Versions') }}</h3>
            @if ($spec && $spec->artworkVersions->isNotEmpty())
                <div class="mb-4 overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('File') }}</th>
                                <th>{{ __('Uploaded') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Notes') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($spec->artworkVersions as $artwork)
                                <tr>
                                    <td>
                                        {{ $artwork->versionLabel() }}
                                        @if ($artwork->is_active_version)<span class="erp-badge">{{ __('Active') }}</span>@endif
                                    </td>
                                    <td class="max-w-[10rem] truncate">{{ $artwork->originalFileName() }}</td>
                                    <td>{{ $artwork->uploaded_at?->format('Y-m-d') }}</td>
                                    <td>{{ $artwork->status->label() }}</td>
                                    <td class="max-w-[8rem] truncate">{{ $artwork->change_notes }}</td>
                                    <td>
                                        @if ($artwork->isPreviewable() && $artwork->previewUrl())
                                            <button
                                                type="button"
                                                class="erp-btn-ghost text-xs min-h-[2.25rem] px-2"
                                                data-preview-url="{{ $artwork->previewUrl() }}"
                                                data-preview-title="{{ $spec->name }}"
                                                data-preview-pdf="{{ $artwork->mime_type === 'application/pdf' ? '1' : '0' }}"
                                                @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                                            >{{ __('Preview') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mb-3 text-sm text-slate-500">{{ __('No artwork versions yet.') }}</p>
            @endif

            @if (! $spec)
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="erp-label" for="artwork_type">{{ __('Artwork type') }}</label>
                        <select id="artwork_type" name="artwork_type" class="erp-input w-full">
                            @foreach ($artworkTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="erp-label" for="artwork_file">{{ __('Initial artwork file') }}</label>
                        <input type="file" id="artwork_file" name="artwork_file" class="erp-input w-full" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label" for="artwork_change_notes">{{ __('Change notes') }}</label>
                        <input id="artwork_change_notes" name="artwork_change_notes" class="erp-input w-full" maxlength="2000">
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ __('Versions are never overwritten. Each upload creates a new version.') }}</p>
            @endif
        </section>
    @endif

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Serial Settings') }}</h3>
        @if ($serialSummary && ($serialSummary['uses_serial_numbers'] ?? false))
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Product default') }}</dt><dd><code>{{ $serialSummary['product_prefix'] }}{{ str_repeat('0', max(0, ($serialSummary['product_padding'] ?? 6) - 1)) }}1</code></dd></div>
                <div><dt class="text-slate-500">{{ __('Next number') }}</dt><dd>{{ $serialSummary['next_number'] ?? '—' }}</dd></div>
                @if ($serialSummary['last_allocation'] ?? null)
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Last allocated range') }}</dt><dd><code>{{ $serialSummary['last_allocation']['prefix'] }}{{ $serialSummary['last_allocation']['start'] }} – {{ $serialSummary['last_allocation']['end'] }}</code></dd></div>
                @endif
            </dl>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="erp-label" for="serial_prefix">{{ __('Customer prefix override') }}</label>
                    <input id="serial_prefix" name="serial_prefix" class="erp-input w-full" maxlength="30"
                        value="{{ old('serial_prefix', $serialProfile?->serial_prefix ?? $serialSummary['customer_prefix'] ?? '') }}"
                        placeholder="{{ $serialSummary['product_prefix'] ?? 'FL-' }}">
                </div>
                <div>
                    <label class="erp-label" for="serial_padding_length">{{ __('Padding length') }}</label>
                    <input type="number" id="serial_padding_length" name="serial_padding_length" class="erp-input w-full" min="1" max="12"
                        value="{{ old('serial_padding_length', $serialProfile?->serial_padding_length ?? $serialSummary['customer_padding'] ?? 6) }}">
                </div>
            </div>
        @else
            <p class="text-sm text-slate-500">{{ __('Serial numbering applies when the linked product is serial-enabled.') }}</p>
        @endif
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Production Notes') }}</h3>
        <textarea name="production_notes" class="erp-input w-full" rows="3">{{ old('production_notes', $spec?->production_notes) }}</textarea>
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Commercial Notes') }}</h3>
        <textarea name="commercial_notes" class="erp-input w-full" rows="3">{{ old('commercial_notes', $spec?->commercial_notes) }}</textarea>
    </section>
</div>
