@php
    $spec = $specification ?? null;
    $customer = $customer ?? null;
    $disabled = (bool) ($disabled ?? false) || ! $customer;
    $required = (bool) ($required ?? (bool) $customer);
    $idPrefix = $idPrefix ?? 'spec-product';
    $finishedProducts = $customer
        ? app(\App\Support\Crm\CustomerPrintSpecificationService::class)->finishedProductOptions($customer)
        : [];
    $currentName = old(
        'product_name',
        $spec?->product_name
            ?? $spec?->inventoryItem?->item_name
            ?? '',
    );
    $currentId = old('inventory_item_id', $spec?->inventory_item_id);
@endphp

<div
    class="relative"
    x-data="{
        query: @js((string) $currentName),
        selectedId: @js($currentId ? (string) $currentId : ''),
        disabled: @js((bool) $disabled),
        open: false,
        highlightIndex: -1,
        options: @js($finishedProducts),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            const list = !q
                ? this.options
                : this.options.filter((option) => option.label.toLowerCase().includes(q) || option.name.toLowerCase().includes(q));
            return list.slice(0, 20);
        },
        pick(option) {
            this.selectedId = String(option.value);
            this.query = option.name;
            this.open = false;
        },
        onType() {
            this.selectedId = '';
            this.open = true;
            this.highlightIndex = 0;
        },
        chooseHighlighted() {
            const option = this.filtered[this.highlightIndex];
            if (option) {
                this.pick(option);
            }
        },
    }"
    @click.outside="open = false"
>
    <label class="erp-label" for="{{ $idPrefix }}-name">{{ __('Product') }}</label>
    <input type="hidden" name="inventory_item_id" :value="selectedId">
    <input
        type="text"
        id="{{ $idPrefix }}-name"
        name="product_name"
        class="erp-input w-full"
        maxlength="255"
        placeholder="{{ __('e.g. A5 brochure, receipt book') }}"
        x-model="query"
        @required($required)
        @disabled($disabled)
        autocomplete="off"
        role="combobox"
        aria-autocomplete="list"
        :aria-expanded="open"
        @focus="open = true"
        @input="onType()"
        @keydown.arrow-down.prevent="open = true; highlightIndex = Math.min(highlightIndex + 1, filtered.length - 1)"
        @keydown.arrow-up.prevent="highlightIndex = Math.max(highlightIndex - 1, 0)"
        @keydown.enter.prevent="chooseHighlighted()"
        @keydown.escape="open = false"
    >
    <div
        x-show="open && !disabled"
        x-cloak
        class="absolute z-40 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-erp-border bg-white shadow-lg"
        role="listbox"
    >
        <template x-for="(option, index) in filtered" :key="option.value">
            <button
                type="button"
                role="option"
                class="flex w-full items-center px-3 py-2 text-left text-sm hover:bg-slate-50"
                :class="highlightIndex === index ? 'bg-slate-100' : ''"
                @mousedown.prevent="pick(option)"
            >
                <span class="truncate" x-text="option.label"></span>
            </button>
        </template>
        <p class="px-3 py-2 text-xs text-slate-500" x-show="filtered.length === 0 && query.trim() !== ''" x-cloak>
            {{ __('No catalogue match — this will be saved as typed.') }}
        </p>
    </div>
</div>
