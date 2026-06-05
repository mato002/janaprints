@php
    $heldSale = $heldSale ?? null;
    $heldCart = $heldCart ?? null;
    $formAction = $heldSale
        ? route('admin.commercial.pos.pay', $heldSale)
        : $storeUrl;
    $cancelUrl = $heldSale ? route('admin.commercial.pos.cancel', $heldSale) : null;
@endphp

<x-admin-layout
    :title="$heldSale ? __('Resume :number', ['number' => $heldSale->sale_number]) : __('Counter Sales')"
    :breadcrumbs="[['label' => __('POS'), 'url' => $dashboardUrl], ['label' => $heldSale ? __('Resume sale') : __('Counter Sales')]]"
>
    @if (empty($activeSession))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ __('Open a POS session before processing sales.') }}
            @can('open', App\Models\Pos\PosSession::class)
                <a href="{{ route('admin.commercial.pos.sessions.create') }}" class="font-semibold underline">{{ __('Open session') }}</a>
            @endcan
        </div>
    @else
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ __('Active session: :number', ['number' => $activeSession->session_number]) }}
        </div>
    @endif

    @if ($heldSale)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Resuming held sale :number — held :time.', [
                'number' => $heldSale->sale_number,
                'time' => $heldSale->hold?->held_at?->format('Y-m-d H:i') ?? $heldSale->updated_at->format('Y-m-d H:i'),
            ]) }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ $formAction }}"
        id="pos-counter-form"
        x-data="posCounterWorkstation(@js([
            'searchUrl' => $searchUrl,
            'heldCart' => $heldCart,
            'canHold' => $canHold,
            'canComplete' => $canComplete,
            'canCancel' => $canCancel,
            'isResume' => (bool) $heldSale,
        ]))"
        @if (empty($activeSession)) class="pointer-events-none opacity-60" @endif
    >
        @csrf
        <input type="hidden" name="action" x-model="action">
        <input type="hidden" name="payment_method" x-model="paymentMethod">
        <input type="hidden" name="payment_reference" x-model="paymentReference">

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            {{-- LEFT: Product search --}}
            <div class="xl:col-span-3 space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Barcode scan') }}</h3>
                    <input
                        type="text"
                        class="erp-input w-full font-mono"
                        placeholder="{{ __('Scan or type barcode…') }}"
                        x-ref="barcodeInput"
                        x-model="barcodeQuery"
                        @keydown.enter.prevent="scanBarcode()"
                        autocomplete="off"
                    >
                </x-admin.card>

                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Product search') }}</h3>
                    <input
                        type="search"
                        class="erp-input w-full"
                        placeholder="{{ __('SKU or product name…') }}"
                        x-model="searchQuery"
                        @input.debounce.300ms="searchProducts()"
                        autocomplete="off"
                    >
                    <div class="mt-3 max-h-80 overflow-y-auto divide-y divide-erp-border" x-show="searchResults.length">
                        <template x-for="product in searchResults" :key="product.id">
                            <button
                                type="button"
                                class="w-full px-2 py-2 text-left text-sm hover:bg-slate-50"
                                @click="addProduct(product)"
                            >
                                <span class="font-medium" x-text="product.name"></span>
                                <span class="block text-xs text-slate-500">
                                    <span x-show="product.sku" x-text="'SKU: ' + product.sku"></span>
                                    <span x-show="product.item_code" x-text="' · ' + product.item_code"></span>
                                    · <span x-text="formatMoney(product.unit_price)"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-2 text-xs text-slate-400" x-show="searchLoading">{{ __('Searching…') }}</p>
                    <p class="mt-2 text-xs text-slate-400" x-show="!searchLoading && searchQuery.length >= 2 && !searchResults.length">{{ __('No products found.') }}</p>
                </x-admin.card>
            </div>

            {{-- CENTER: Cart --}}
            <div class="xl:col-span-6">
                <x-admin.card class="!p-0 overflow-hidden">
                    <div class="border-b border-erp-border px-4 py-3">
                        <h3 class="font-semibold">{{ __('Shopping cart') }}</h3>
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
                                        <td class="px-4 py-2">
                                            <input type="hidden" :name="`lines[${index}][inventory_item_id]`" :value="line.item_id || ''">
                                            <input
                                                type="text"
                                                :name="`lines[${index}][description]`"
                                                class="erp-input w-full text-sm"
                                                x-model="line.description"
                                                required
                                            >
                                        </td>
                                        <td class="px-2 py-2">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="decreaseQty(index)">−</button>
                                                <input
                                                    type="number"
                                                    step="0.001"
                                                    min="0.001"
                                                    :name="`lines[${index}][quantity]`"
                                                    class="erp-input w-16 text-center text-sm"
                                                    x-model.number="line.quantity"
                                                >
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="increaseQty(index)">+</button>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :name="`lines[${index}][unit_price]`"
                                                class="erp-input w-full text-sm"
                                                x-model.number="line.unit_price"
                                            >
                                        </td>
                                        <td class="px-2 py-2">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :name="`lines[${index}][discount_amount]`"
                                                class="erp-input w-full text-sm"
                                                x-model.number="line.discount_amount"
                                            >
                                        </td>
                                        <td class="px-2 py-2">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :name="`lines[${index}][tax_amount]`"
                                                class="erp-input w-full text-sm"
                                                x-model.number="line.tax_amount"
                                            >
                                        </td>
                                        <td class="px-2 py-2 text-right tabular-nums font-medium" x-text="formatMoney(lineTotal(line))"></td>
                                        <td class="px-2 py-2">
                                            <button type="button" class="text-red-600 text-xs" @click="removeLine(index)" title="{{ __('Remove item') }}">×</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-erp-border px-4 py-3 text-sm text-slate-500" x-show="!lines.length">
                        {{ __('Scan or search products to add items to the cart.') }}
                    </div>
                </x-admin.card>
            </div>

            {{-- RIGHT: Summary & payment --}}
            <div class="xl:col-span-3 space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 font-semibold">{{ __('Sale summary') }}</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd class="tabular-nums" x-text="formatMoney(subtotal)"></dd></div>
                        <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd class="tabular-nums" x-text="formatMoney(totalDiscount)"></dd></div>
                        <div class="flex justify-between"><dt>{{ __('Tax') }}</dt><dd class="tabular-nums" x-text="formatMoney(totalTax)"></dd></div>
                        <div class="flex justify-between border-t border-erp-border pt-2 text-base font-bold">
                            <dt>{{ __('Grand total') }}</dt>
                            <dd class="tabular-nums" x-text="formatMoney(grandTotal)"></dd>
                        </div>
                    </dl>
                    <div class="mt-3 space-y-2">
                        <label class="text-xs text-slate-500">{{ __('Order discount') }}</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" class="erp-input w-full" x-model.number="saleDiscount">
                        <label class="text-xs text-slate-500">{{ __('Order tax') }}</label>
                        <input type="number" step="0.01" min="0" name="tax_amount" class="erp-input w-full" x-model.number="saleTax">
                    </div>
                </x-admin.card>

                <x-admin.card>
                    <h3 class="mb-3 font-semibold">{{ __('Customer') }}</h3>
                    <label class="flex items-center gap-2 text-sm mb-2">
                        <input type="checkbox" name="is_walk_in" value="1" x-model="walkIn" @change="walkIn && (customerId = '')">
                        {{ __('Walk-in customer') }}
                    </label>
                    <select name="customer_id" class="erp-input w-full" x-model="customerId" :disabled="walkIn">
                        <option value="">{{ __('Select existing customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                    @can('create', App\Models\Crm\Customer::class)
                        <a href="{{ $customerCreateUrl }}" target="_blank" class="mt-2 inline-block text-xs text-erp-primary underline">{{ __('Add customer') }}</a>
                    @endcan
                </x-admin.card>

                <x-admin.card>
                    <h3 class="mb-3 font-semibold">{{ __('Payment') }}</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="erp-btn-secondary text-sm" :class="paymentMethod === 'cash' && 'ring-2 ring-erp-primary'" @click="selectPayment('cash')">{{ __('Cash') }}</button>
                        <button type="button" class="erp-btn-secondary text-sm" :class="paymentMethod === 'mpesa' && 'ring-2 ring-erp-primary'" @click="selectPayment('mpesa')">{{ __('M-Pesa') }}</button>
                        <button type="button" class="erp-btn-secondary text-sm" :class="paymentMethod === 'card' && 'ring-2 ring-erp-primary'" @click="selectPayment('card')">{{ __('Card') }}</button>
                        <button type="button" class="erp-btn-secondary text-sm" :class="paymentMethod === 'bank' && 'ring-2 ring-erp-primary'" @click="selectPayment('bank')">{{ __('Bank') }}</button>
                    </div>
                    <button type="button" class="erp-btn-secondary mt-2 w-full text-sm" @click="showSplitPayment = !showSplitPayment">{{ __('Split payment') }}</button>
                    <div class="mt-2 rounded border border-dashed border-erp-border bg-slate-50 p-3 text-xs text-slate-500" x-show="showSplitPayment" x-cloak>
                        {{ __('Split payment extension point — multi-tender checkout will plug in here.') }}
                    </div>
                    <input type="text" class="erp-input mt-2 w-full text-sm" placeholder="{{ __('Payment reference (optional)') }}" x-model="paymentReference">
                </x-admin.card>

                <x-admin.card>
                    <div class="flex flex-col gap-2">
                        @if ($canHold && ! $heldSale)
                            <button type="button" class="erp-btn-secondary w-full" @click="submitSale('hold')" :disabled="!lines.length">{{ __('Hold sale') }}</button>
                        @endif
                        @if ($canCancel)
                            @if ($heldSale)
                                <button type="submit" formaction="{{ $cancelUrl }}" formmethod="POST" class="erp-btn-secondary w-full text-red-700" onclick="return confirm(@js(__('Cancel this held sale?')))">{{ __('Cancel sale') }}</button>
                            @else
                                <button type="button" class="erp-btn-secondary w-full text-red-700" @click="clearCart()">{{ __('Cancel sale') }}</button>
                            @endif
                        @endif
                        @if ($canComplete)
                            <button type="button" class="erp-btn-primary w-full" @click="submitSale('pay')" :disabled="!lines.length || !paymentMethod">{{ __('Complete sale') }}</button>
                        @endif
                        <a href="{{ $dashboardUrl }}" class="erp-btn-secondary w-full text-center">{{ __('Back to dashboard') }}</a>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    @include('admin.commercial.pos.partials.counter-sales-script')
</x-admin-layout>
