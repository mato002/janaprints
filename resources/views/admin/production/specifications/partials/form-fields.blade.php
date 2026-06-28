@php
    $value = $prefill ?? fn (string $field, mixed $default = null) => old($field, $specification?->{$field} ?? $default);
    $checked = fn (string $field) => (bool) old($field, $specification?->{$field} ?? ($templateDefaults[$field] ?? false));
@endphp

@if (! ($specification ?? null) && ($printTemplates ?? collect())->isNotEmpty())
    <x-admin.card class="mb-6">
        <h3 class="mb-3 font-medium">{{ __('Apply print product template') }}</h3>
        <p class="mb-3 text-sm text-slate-600">{{ __('Select a template to pre-fill manufacturing defaults. All fields remain editable.') }}</p>
        <select
            class="erp-input w-full max-w-xl"
            onchange="if (this.value) { window.location = '{{ route('admin.sales-orders.items.specification.create', [$salesOrder ?? request()->route('salesOrder'), $salesOrderItem ?? request()->route('salesOrderItem')]) }}?template_id=' + this.value; }"
        >
            <option value="">{{ __('Choose template (optional)…') }}</option>
            @foreach ($printTemplates as $tpl)
                <option value="{{ $tpl->id }}" @selected((string) ($selectedTemplateId ?? '') === (string) $tpl->id)>{{ $tpl->name }} ({{ $tpl->code }})</option>
            @endforeach
        </select>
        <input type="hidden" name="print_product_template_id" value="{{ $value('print_product_template_id', $selectedTemplateId ?? null) }}">
    </x-admin.card>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Product') }}</h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label">{{ __('Production type') }}</label>
                <select name="production_type" class="erp-input w-full">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($productionTypes as $type)
                        <option value="{{ $type->value }}" @selected($value('production_type') === $type->value)>{{ str_replace('_', ' ', ucfirst($type->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Description') }}</label>
                <textarea name="product_description" class="erp-input w-full" rows="2">{{ $value('product_description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label">{{ __('Quantity') }}</label>
                    <input type="number" step="0.001" name="quantity" value="{{ $value('quantity') }}" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label">{{ __('Unit') }}</label>
                    <input type="text" name="unit" value="{{ $value('unit') }}" class="erp-input w-full" placeholder="copies">
                </div>
            </div>
            <div>
                <label class="erp-label">{{ __('Approval status') }}</label>
                <select name="approval_status" class="erp-input w-full">
                    @foreach ($approvalStatuses as $status)
                        <option value="{{ $status->value }}" @selected($value('approval_status', 'draft') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Dimensions') }}</h3>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="erp-label">{{ __('Size') }}</label><input type="text" name="size" value="{{ $value('size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Finished size') }}</label><input type="text" name="finished_size" value="{{ $value('finished_size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Sheet size') }}</label><input type="text" name="sheet_size" value="{{ $value('sheet_size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Orientation') }}</label><input type="text" name="orientation" value="{{ $value('orientation') }}" class="erp-input w-full" placeholder="portrait"></div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Materials') }}</h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label">{{ __('Paper') }}</label>
                <select name="paper_inventory_item_id" class="erp-input w-full">
                    <option value="">{{ __('Select paper…') }}</option>
                    @foreach ($paperItems as $item)
                        <option value="{{ $item->id }}" @selected((string) $value('paper_inventory_item_id') === (string) $item->id)>{{ $item->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Material') }}</label>
                <select name="material_inventory_item_id" class="erp-input w-full">
                    <option value="">{{ __('Select material…') }}</option>
                    @foreach ($materialItems as $item)
                        <option value="{{ $item->id }}" @selected((string) $value('material_inventory_item_id') === (string) $item->id)>{{ $item->item_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Print') }}</h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label">{{ __('Ink type') }}</label>
                <select name="ink_type" class="erp-input w-full">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($inkTypes as $ink)
                        <option value="{{ $ink->value }}" @selected($value('ink_type') === $ink->value)>{{ $ink->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Ink profile') }}</label>
                <select name="ink_profile_id" class="erp-input w-full">
                    <option value="">{{ __('Select…') }}</option>
                    @foreach ($inkProfiles as $profile)
                        <option value="{{ $profile->id }}" @selected((string) $value('ink_profile_id') === (string) $profile->id)>{{ $profile->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label">{{ __('Colour mode') }}</label><input type="text" name="colour_mode" value="{{ $value('colour_mode') }}" class="erp-input w-full"></div>
                <div><label class="erp-label">{{ __('Sides') }}</label><input type="text" name="sides" value="{{ $value('sides') }}" class="erp-input w-full" placeholder="single / double"></div>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Finishing') }}</h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label">{{ __('Binding') }}</label><input type="text" name="binding_type" value="{{ $value('binding_type') }}" class="erp-input w-full"></div>
                <div><label class="erp-label">{{ __('Finishing type') }}</label><input type="text" name="finishing_type" value="{{ $value('finishing_type') }}" class="erp-input w-full"></div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                @foreach (['lamination', 'foiling', 'spot_uv', 'embossing', 'debossing', 'die_cutting', 'creasing', 'perforation', 'numbering_required', 'eyelets'] as $option)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="{{ $option }}" value="1" @checked($checked($option))>
                        {{ str_replace('_', ' ', ucfirst($option)) }}
                    </label>
                @endforeach
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Imposition') }}</h3>
        <div class="grid grid-cols-3 gap-3">
            <div><label class="erp-label">{{ __('Ups') }}</label><input type="number" min="1" name="ups" value="{{ $value('ups') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Estimated sheets') }}</label><input type="number" min="0" name="estimated_sheets" value="{{ $value('estimated_sheets') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Waste %') }}</label><input type="number" step="0.01" min="0" max="100" name="waste_allowance_percent" value="{{ $value('waste_allowance_percent') }}" class="erp-input w-full"></div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Artwork & notes') }}</h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label">{{ __('Artwork reference') }}</label><input type="text" name="artwork_reference" value="{{ $value('artwork_reference') }}" class="erp-input w-full"></div>
                <div><label class="erp-label">{{ __('Artwork version') }}</label><input type="text" name="artwork_version" value="{{ $value('artwork_version') }}" class="erp-input w-full"></div>
            </div>
            <div><label class="erp-label">{{ __('Production notes') }}</label><textarea name="production_notes" class="erp-input w-full" rows="2">{{ $value('production_notes') }}</textarea></div>
            <div><label class="erp-label">{{ __('Delivery notes') }}</label><textarea name="delivery_notes" class="erp-input w-full" rows="2">{{ $value('delivery_notes') }}</textarea></div>
        </div>
    </x-admin.card>
</div>
