@php
    $value = fn (string $field, mixed $default = null) => old($field, $template?->{$field} ?? $default);
    $checked = fn (string $field) => (bool) old($field, $template?->{$field} ?? false);
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('General') }}</h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label">{{ __('Code') }}</label><input type="text" name="code" value="{{ $value('code') }}" class="erp-input w-full" required></div>
                <div><label class="erp-label">{{ __('Name') }}</label><input type="text" name="name" value="{{ $value('name') }}" class="erp-input w-full" required></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label">{{ __('Category') }}</label>
                    <select name="category" class="erp-input w-full" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->value }}" @selected($value('category') === $cat->value)>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Production type') }}</label>
                    <select name="production_type" class="erp-input w-full">
                        <option value="">{{ __('Select…') }}</option>
                        @foreach ($productionTypes as $type)
                            <option value="{{ $type->value }}" @selected($value('production_type') === $type->value)>{{ str_replace('_', ' ', ucfirst($type->value)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="2">{{ $value('description') }}</textarea></div>
            @if ($template ?? null)
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($checked('is_active'))> {{ __('Active') }}</label>
            @endif
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Manufacturing defaults') }}</h3>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="erp-label">{{ __('GSM') }}</label><input type="text" name="gsm" value="{{ $value('gsm') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Size') }}</label><input type="text" name="default_size" value="{{ $value('default_size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Finished size') }}</label><input type="text" name="default_finished_size" value="{{ $value('default_finished_size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Sheet size') }}</label><input type="text" name="default_sheet_size" value="{{ $value('default_sheet_size') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Orientation') }}</label><input type="text" name="default_orientation" value="{{ $value('default_orientation') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Colour mode') }}</label><input type="text" name="default_colour_mode" value="{{ $value('default_colour_mode') }}" class="erp-input w-full" placeholder="4/4"></div>
            <div><label class="erp-label">{{ __('Colours') }}</label><input type="number" min="1" max="16" name="number_of_colours" value="{{ $value('number_of_colours') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Sides') }}</label><input type="text" name="default_sides" value="{{ $value('default_sides') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Binding') }}</label><input type="text" name="default_binding_type" value="{{ $value('default_binding_type') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Finishing') }}</label><input type="text" name="default_finishing_type" value="{{ $value('default_finishing_type') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Ups') }}</label><input type="number" name="default_ups" value="{{ $value('default_ups') }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Waste %') }}</label><input type="number" step="0.01" name="default_waste_allowance_percent" value="{{ $value('default_waste_allowance_percent') }}" class="erp-input w-full"></div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
            @foreach (['default_lamination', 'default_foiling', 'default_spot_uv', 'default_embossing', 'default_debossing', 'default_die_cutting', 'default_creasing', 'default_perforation', 'default_numbering_required', 'default_eyelets'] as $option)
                <label class="flex items-center gap-2"><input type="checkbox" name="{{ $option }}" value="1" @checked($checked($option))> {{ str_replace(['default_', '_'], ['', ' '], ucfirst($option)) }}</label>
            @endforeach
        </div>
        <div class="mt-3 space-y-3">
            <div>
                <label class="erp-label">{{ __('Default paper') }}</label>
                <select name="default_paper_inventory_item_id" class="erp-input w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($paperItems as $item)
                        <option value="{{ $item->id }}" @selected((string) $value('default_paper_inventory_item_id') === (string) $item->id)>{{ $item->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="erp-label">{{ __('Default notes') }}</label><textarea name="default_notes" class="erp-input w-full" rows="2">{{ $value('default_notes') }}</textarea></div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Artwork guidance') }}</h3>
        <div class="space-y-3">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="artwork_required" value="1" @checked(old('artwork_required', $template->artwork_required ?? true))> {{ __('Artwork required') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="bleed_required" value="1" @checked($checked('bleed_required'))> {{ __('Bleed required') }}</label>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label">{{ __('Safe margin') }}</label><input type="text" name="safe_margin" value="{{ $value('safe_margin') }}" class="erp-input w-full"></div>
                <div><label class="erp-label">{{ __('Resolution') }}</label><input type="text" name="resolution_recommendation" value="{{ $value('resolution_recommendation') }}" class="erp-input w-full"></div>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-4 font-medium">{{ __('Routing recommendations') }}</h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label">{{ __('Preferred work center') }}</label>
                <select name="preferred_work_center_id" class="erp-input w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($workCenters as $wc)
                        <option value="{{ $wc->id }}" @selected((string) $value('preferred_work_center_id') === (string) $wc->id)>{{ $wc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="erp-label">{{ __('Operator skill') }}</label><input type="text" name="preferred_operator_skill" value="{{ $value('preferred_operator_skill') }}" class="erp-input w-full"></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="optional_outsource" value="1" @checked($checked('optional_outsource'))> {{ __('Outsourcing optional') }}</label>
            <div><label class="erp-label">{{ __('Recommended packaging') }}</label><textarea name="recommended_packaging" class="erp-input w-full" rows="2">{{ $value('recommended_packaging') }}</textarea></div>
        </div>
    </x-admin.card>
</div>
