<div class="mb-4">
    <x-admin.card>
        <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Item quick lookup') }}</h2>
        <p class="mb-3 text-xs text-slate-500">{{ __('Search SKU or name to see operational stock status.') }}</p>

        <div class="relative">
            <input
                type="search"
                x-model="query"
                @focus="openDropdown()"
                @input="onInput()"
                @keydown.escape="closeDropdown()"
                class="erp-input w-full"
                placeholder="{{ __('Search SKU or item name…') }}"
                autocomplete="off"
            >

            <div
                x-show="open && (loading || results.length > 0 || query.trim() !== '')"
                x-cloak
                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-erp-border bg-white shadow-lg"
            >
                <div x-show="loading" class="px-4 py-3 text-sm text-slate-500">{{ __('Searching…') }}</div>
                <template x-for="row in results" :key="row.id">
                    <button
                        type="button"
                        class="block w-full border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-b-0"
                        @click="selectItem(row)"
                    >
                        <span class="block font-medium text-slate-900" x-text="row.name"></span>
                        <span class="block font-mono text-xs text-slate-500" x-text="row.sku"></span>
                    </button>
                </template>
                <div x-show="! loading && results.length === 0 && query.trim() !== ''" class="px-4 py-3 text-sm text-slate-500">{{ __('No items found.') }}</div>
            </div>
        </div>

        <div x-show="selected" x-cloak class="mt-4 rounded-lg border border-erp-border bg-slate-50/80 p-4">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-900" x-text="selected?.name"></p>
                    <p class="font-mono text-xs text-slate-500" x-text="selected?.sku"></p>
                </div>
                <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-700" @click="clearSelection()">{{ __('Clear') }}</button>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Available') }}</dt>
                    <dd class="font-semibold tabular-nums text-emerald-700" x-text="formatQty(selected?.available)"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Reserved') }}</dt>
                    <dd class="font-semibold tabular-nums text-amber-700" x-text="formatQty(selected?.reserved)"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Free') }}</dt>
                    <dd class="font-semibold tabular-nums text-erp-primary" x-text="formatQty(selected?.free)"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Minimum') }}</dt>
                    <dd class="font-semibold tabular-nums text-slate-700" x-text="formatQty(selected?.minimum)"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Incoming') }}</dt>
                    <dd class="font-semibold tabular-nums text-indigo-700" x-text="formatQty(selected?.incoming)"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Warehouse') }}</dt>
                    <dd class="font-medium text-slate-700" x-text="selected?.warehouse ?? '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('Shelf') }}</dt>
                    <dd class="font-medium text-slate-700" x-text="selected?.shelf ?? '—'"></dd>
                </div>
            </dl>
        </div>
    </x-admin.card>
</div>
