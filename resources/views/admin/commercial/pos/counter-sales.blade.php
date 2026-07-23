<x-admin-layout
    :title="__('Counter Sales')"
    :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Counter Sales')]]"
>
    @include('admin.commercial.pos.partials.desk-mode-nav', ['activePosView' => \App\Support\Commercial\PosDeskViews::COUNTER])

    <div
        id="pos-counter-root"
        x-data="posCounterWorkstation(@js($workstationConfig))"
        x-init="init()"
        class="relative"
        :class="!hasSession && 'pos-counter--locked'"
    >
        @include('admin.commercial.pos.partials.workstation.session-widget')

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" class="erp-btn-secondary text-sm" @click="openHeldDrawer()" x-show="permissions.canHold || permissions.canComplete">
                {{ __('Held sales') }}
                <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs" x-text="heldCount" x-show="heldCount > 0"></span>
            </button>
            <a href="{{ route('admin.commercial.pos.dashboard') }}" class="erp-btn-secondary text-sm">{{ __('POS dashboard') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12" :class="!hasSession && 'pointer-events-none opacity-60'">
            <div class="xl:col-span-3 space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Barcode scan') }}</h3>
                    <input type="text" class="erp-input w-full font-mono" placeholder="{{ __('Scan or type barcode…') }}" x-ref="barcodeInput" x-model="barcodeQuery" @keydown.enter.prevent="scanBarcode()" autocomplete="off">
                </x-admin.card>
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Product search') }}</h3>
                    <input type="search" class="erp-input w-full" placeholder="{{ __('SKU or product name…') }}" x-model="searchQuery" @input.debounce.300ms="searchProducts()" autocomplete="off">
                    <div class="mt-3 max-h-80 overflow-y-auto divide-y divide-erp-border" x-show="searchResults.length">
                        <template x-for="product in searchResults" :key="product.id">
                            <button type="button" class="w-full px-2 py-2 text-left text-sm hover:bg-slate-50" @click="addProduct(product)">
                                <span class="font-medium" x-text="product.name"></span>
                                <span class="block text-xs text-slate-500">
                                    <span x-show="product.sku" x-text="'SKU: ' + product.sku"></span>
                                    · <span x-text="formatMoney(product.unit_price)"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-2 text-xs text-slate-400" x-show="searchLoading">{{ __('Searching…') }}</p>
                </x-admin.card>
            </div>

            <div class="xl:col-span-6">
                <x-admin.card class="!p-0 overflow-hidden">
                    <div class="border-b border-erp-border px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold">{{ __('Shopping cart') }}</h3>
                        <span class="text-xs text-amber-700" x-show="isResume" x-text="resumeLabel"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-2">{{ __('Product') }}</th>
                                    <th class="px-2 py-2 w-24">{{ __('Qty') }}</th>
                                    <th class="px-2 py-2 w-28">{{ __('Unit price') }}</th>
                                    <th class="px-2 py-2 w-24">{{ __('Discount') }}</th>
                                    <th class="px-2 py-2 w-24">{{ __('Tax') }}</th>
                                    <th class="px-2 py-2 w-28 text-right">{{ __('Line total') }}</th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, index) in lines" :key="index">
                                    <tr class="border-t border-erp-border">
                                        <td class="px-4 py-2"><input type="text" class="erp-input w-full text-sm" x-model="line.description" required></td>
                                        <td class="px-2 py-2">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="decreaseQty(index)">−</button>
                                                <input type="number" step="0.001" min="0.001" class="erp-input w-16 text-center text-sm" x-model.number="line.quantity">
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="increaseQty(index)">+</button>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.unit_price"></td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.discount_amount"></td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.tax_amount"></td>
                                        <td class="px-2 py-2 text-right tabular-nums font-medium" x-text="formatMoney(lineTotal(line))"></td>
                                        <td class="px-2 py-2"><button type="button" class="text-red-600 text-xs" @click="removeLine(index)">×</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-erp-border px-4 py-3 text-sm text-slate-500" x-show="!lines.length">{{ __('Scan or search products to add items to the cart.') }}</div>
                </x-admin.card>
            </div>

            <div class="xl:col-span-3 space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 font-semibold">{{ __('Sale summary') }}</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd class="tabular-nums" x-text="formatMoney(subtotal)"></dd></div>
                        <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd class="tabular-nums" x-text="formatMoney(totalDiscount)"></dd></div>
                        <div class="flex justify-between"><dt>{{ __('Tax') }}</dt><dd class="tabular-nums" x-text="formatMoney(totalTax)"></dd></div>
                        <div class="flex justify-between border-t border-erp-border pt-2 text-base font-bold">
                            <dt>{{ __('Grand total') }}</dt><dd class="tabular-nums" x-text="formatMoney(grandTotal)"></dd>
                        </div>
                    </dl>
                    <div class="mt-3 space-y-2">
                        <label class="text-xs text-slate-500">{{ __('Order discount') }}</label>
                        <input type="number" step="0.01" min="0" class="erp-input w-full" x-model.number="saleDiscount">
                        <label class="text-xs text-slate-500">{{ __('Order tax') }}</label>
                        <input type="number" step="0.01" min="0" class="erp-input w-full" x-model.number="saleTax">
                    </div>
                </x-admin.card>

                <x-admin.card>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold">{{ __('Customer') }}</h3>
                        <button type="button" class="text-xs font-medium text-erp-primary" @click="showCustomerModal = true">{{ __('Change') }}</button>
                    </div>
                    <p class="text-sm" x-text="customerLabel"></p>
                </x-admin.card>

                <x-admin.card>
                    <div class="flex flex-col gap-2">
                        <button type="button" class="erp-btn-secondary w-full" @click="submitHold()" :disabled="!lines.length || !permissions.canHold || isResume" x-show="permissions.canHold">{{ __('Hold sale') }}</button>
                        <button type="button" class="erp-btn-secondary w-full text-red-700" @click="cancelSale()" x-show="permissions.canCancel">{{ __('Cancel sale') }}</button>
                        <button type="button" class="erp-btn-primary w-full" @click="openPaymentModal()" :disabled="!lines.length || !permissions.canComplete">{{ __('Complete sale') }}</button>
                    </div>
                </x-admin.card>
            </div>
        </div>

        @include('admin.commercial.pos.partials.workstation.modals')
        @include('admin.commercial.pos.partials.workstation.drawers')
    </div>

    @include('admin.commercial.pos.partials.counter-sales-script')
</x-admin-layout>
