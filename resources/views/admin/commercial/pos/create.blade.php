<x-admin-layout :title="__('New POS sale')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('New sale')]]">
    @if (empty($activeSession))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ __('Open a POS session before recording counter sales.') }}
            @can('open', App\Models\Pos\PosSession::class)
                <a href="{{ route('admin.commercial.pos.sessions.create') }}" class="font-semibold underline">{{ __('Open session') }}</a>
            @endcan
        </div>
    @else
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ __('Active session: :number', ['number' => $activeSession->session_number]) }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.commercial.pos.store') }}" x-data="posCart(@js($items->map(fn ($i) => ['id' => $i->id, 'name' => $i->item_name, 'price' => (float) $i->standard_cost])->values()))" @if (empty($activeSession)) class="pointer-events-none opacity-60" @endif>
        @csrf
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 font-medium">{{ __('Cart') }}</h3>
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="mb-3 grid grid-cols-12 gap-2 border-b border-erp-border pb-3 text-sm">
                            <div class="col-span-5">
                                <select :name="`lines[${index}][inventory_item_id]`" class="erp-input w-full text-xs" x-model="line.item_id" @change="applyItem(index)">
                                    <option value="">{{ __('Custom line') }}</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" :name="`lines[${index}][description]`" class="erp-input mt-1 w-full" x-model="line.description" required>
                            </div>
                            <div class="col-span-2"><input type="number" step="0.001" min="0.001" :name="`lines[${index}][quantity]`" class="erp-input w-full" x-model.number="line.quantity"></div>
                            <div class="col-span-2"><input type="number" step="0.01" min="0" :name="`lines[${index}][unit_price]`" class="erp-input w-full" x-model.number="line.unit_price"></div>
                            <div class="col-span-2"><input type="number" step="0.01" min="0" :name="`lines[${index}][discount_amount]`" class="erp-input w-full" x-model.number="line.discount_amount"></div>
                            <div class="col-span-1 flex items-center"><button type="button" class="text-red-600 text-xs" @click="removeLine(index)" x-show="lines.length > 1">{{ __('×') }}</button></div>
                            <input type="hidden" :name="`lines[${index}][tax_amount]`" :value="line.tax_amount">
                        </div>
                    </template>
                    <button type="button" class="erp-btn-secondary text-xs" @click="addLine()">{{ __('Add line') }}</button>
                </x-admin.card>
            </div>
            <div class="space-y-4">
                <x-admin.card>
                    <h3 class="mb-3 font-medium">{{ __('Customer') }}</h3>
                    <label class="flex items-center gap-2 text-sm mb-2">
                        <input type="checkbox" name="is_walk_in" value="1" x-model="walkIn" @change="walkIn && (customerId = '')">
                        {{ __('Walk-in customer') }}
                    </label>
                    <select name="customer_id" class="erp-input w-full" x-model="customerId" :disabled="walkIn">
                        <option value="">{{ __('Select customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                </x-admin.card>
                <x-admin.card>
                    <h3 class="mb-3 font-medium">{{ __('Totals') }}</h3>
                    <div class="space-y-2 text-sm">
                        <label>{{ __('Sale discount') }}<input type="number" step="0.01" min="0" name="discount_amount" class="erp-input mt-1 w-full" x-model.number="saleDiscount"></label>
                        <label>{{ __('Tax') }}<input type="number" step="0.01" min="0" name="tax_amount" class="erp-input mt-1 w-full" x-model.number="saleTax"></label>
                        <p class="flex justify-between border-t border-erp-border pt-2 font-semibold"><span>{{ __('Total') }}</span><span x-text="formatMoney(grandTotal)"></span></p>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm">{{ __('Payment method') }}</label>
                        <select name="payment_method" class="erp-input mt-1 w-full">
                            <option value="">{{ __('— On complete —') }}</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}">{{ ucfirst(str_replace('_', ' ', $method->value)) }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="payment_reference" class="erp-input mt-2 w-full text-sm" placeholder="{{ __('Payment reference (optional)') }}">
                    </div>
                    <div class="mt-4 flex flex-col gap-2">
                        <button type="submit" name="action" value="pay" class="erp-btn-primary w-full">{{ __('Complete & pay') }}</button>
                        <button type="submit" name="action" value="hold" class="erp-btn-secondary w-full">{{ __('Hold sale') }}</button>
                        <button type="submit" name="action" value="save" class="erp-btn-secondary w-full">{{ __('Save draft') }}</button>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    <script>
        function posCart(catalog) {
            return {
                catalog,
                lines: [{ item_id: '', description: '', quantity: 1, unit_price: 0, discount_amount: 0, tax_amount: 0 }],
                saleDiscount: 0,
                saleTax: 0,
                walkIn: true,
                customerId: '',
                addLine() { this.lines.push({ item_id: '', description: '', quantity: 1, unit_price: 0, discount_amount: 0, tax_amount: 0 }); },
                removeLine(i) { this.lines.splice(i, 1); },
                applyItem(i) {
                    const item = this.catalog.find(p => String(p.id) === String(this.lines[i].item_id));
                    if (item) { this.lines[i].description = item.name; this.lines[i].unit_price = item.price; }
                },
                get subtotal() {
                    return this.lines.reduce((sum, l) => sum + Math.max(0, (l.quantity * l.unit_price) - (l.discount_amount || 0)), 0);
                },
                get grandTotal() { return Math.max(0, this.subtotal - (this.saleDiscount || 0) + (this.saleTax || 0)); },
                formatMoney(v) { return Number(v || 0).toFixed(2); },
            };
        }
    </script>
</x-admin-layout>
