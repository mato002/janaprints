@php
    $inputClass = $inputClass ?? 'erp-input w-full min-h-[2.75rem]';
@endphp

<div class="erp-searchable-select relative min-w-0 flex-1" @click.outside="closeDropdown()">
    <input
        type="text"
        class="{{ $inputClass }} erp-searchable-select__input pr-9"
        :value="open ? query : selectedLabel"
        :placeholder="comboboxPlaceholder"
        :disabled="comboboxDisabled"
        :readonly="comboboxReadonly"
        role="combobox"
        aria-autocomplete="list"
        autocomplete="off"
        :aria-expanded="open"
        @focus="openDropdown()"
        @click="openDropdown()"
        @input="onComboboxInput($event)"
        @keydown="onComboboxKeydown($event)"
    >
    <x-admin.icon
        name="chevron-down"
        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
    />
    <div
        x-show="open"
        x-cloak
        class="absolute z-40 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-erp-border bg-white shadow-lg"
        role="listbox"
    >
        <template x-if="filteredOptions.length === 0">
            <p class="px-3 py-3 text-sm text-slate-500">{{ __('No matches.') }}</p>
        </template>
        <template x-for="(option, index) in filteredOptions" :key="`${option.value}-${index}`">
            <button
                type="button"
                role="option"
                class="flex w-full items-center px-3 py-2 text-left text-sm transition hover:bg-slate-50"
                :class="{
                    'bg-erp-accent/10 text-erp-accent': String(selected) === String(option.value),
                    'bg-slate-100': highlightIndex === index,
                }"
                @mousedown.prevent="selectComboboxOption(option)"
            >
                <span class="truncate" x-text="option.label"></span>
            </button>
        </template>
    </div>
</div>
