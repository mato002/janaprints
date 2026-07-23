<?php
    $defaultTaxRate = (float) app(\App\Support\Platform\SystemSettingsService::class)->get(
        'default_tax_rate',
        config('settings_registry.sections.company.settings.default_tax_rate.default', 16),
        ($quotation ?? null)?->company_id ?? tenant()->companyId() ?? auth()->user()?->company_id,
    );

    $normalizeRow = function (array $row): array {
        return [
            'item_type' => $row['item_type'] ?? 'product',
            'item_name' => $row['item_name'] ?? '',
            'description' => $row['description'] ?? null,
            'quantity' => $row['quantity'] ?? 1,
            'unit_price' => $row['unit_price'] ?? 0,
            'discount' => $row['discount'] ?? 0,
            'apply_tax' => array_key_exists('apply_tax', $row)
                ? filter_var($row['apply_tax'], FILTER_VALIDATE_BOOLEAN)
                : ((float) ($row['tax_rate'] ?? 0) > 0),
        ];
    };

    $defaultRows = [[
        'item_type' => 'product',
        'item_name' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'discount' => 0,
        'apply_tax' => true,
    ]];

    $source = old('items', isset($quotation)
        ? $quotation->items->map(fn ($i) => [
            'item_type' => $i->item_type->value,
            'item_name' => $i->item_name,
            'description' => $i->description,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
            'discount' => $i->discount,
            'tax_rate' => $i->tax_rate,
        ])->toArray()
        : $defaultRows);

    $rows = collect($source)->map($normalizeRow)->values()->all();
?>

<div
    class="space-y-3"
    x-data="{
        rows: <?php echo \Illuminate\Support\Js::from($rows)->toHtml() ?>,
        defaultTaxRate: <?php echo \Illuminate\Support\Js::from($defaultTaxRate)->toHtml() ?>,
        lineSubtotal(row) {
            const qty = Number(row.quantity) || 0;
            const price = Number(row.unit_price) || 0;
            const discount = Number(row.discount) || 0;
            return Math.max(0, (qty * price) - discount);
        },
        lineTax(row) {
            if (! row.apply_tax) {
                return 0;
            }
            return this.lineSubtotal(row) * (this.defaultTaxRate / 100);
        },
        lineTotal(row) {
            return this.lineSubtotal(row) + this.lineTax(row);
        },
        effectiveTaxRate(row) {
            return row.apply_tax ? this.defaultTaxRate : 0;
        },
        get grandTotal() {
            return this.rows.reduce((sum, row) => sum + this.lineTotal(row), 0);
        },
        formatMoney(value) {
            return Number(value || 0).toFixed(2);
        },
    }"
>
    <template x-for="(row, index) in rows" :key="index">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-type-'+index"><?php echo e(__('Type')); ?></label>
                <select
                    :id="'quotation-item-type-'+index"
                    :name="'items['+index+'][item_type]'"
                    class="erp-input w-full"
                    x-model="row.item_type"
                >
                    <?php $__currentLoopData = $itemTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>"><?php echo e($type->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="min-w-0 sm:col-span-2 lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-name-'+index"><?php echo e(__('Item name')); ?></label>
                <input
                    type="text"
                    :id="'quotation-item-name-'+index"
                    :name="'items['+index+'][item_name]'"
                    class="erp-input w-full"
                    placeholder="<?php echo e(__('e.g. A5 Flyers — 2000 pcs')); ?>"
                    x-model="row.item_name"
                    required
                >
            </div>
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-qty-'+index"><?php echo e(__('Quantity')); ?></label>
                <input
                    type="number"
                    step="0.001"
                    min="0.001"
                    :id="'quotation-item-qty-'+index"
                    :name="'items['+index+'][quantity]'"
                    class="erp-input w-full"
                    x-model="row.quantity"
                    required
                >
            </div>
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-price-'+index"><?php echo e(__('Unit price')); ?></label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    :id="'quotation-item-price-'+index"
                    :name="'items['+index+'][unit_price]'"
                    class="erp-input w-full"
                    x-model="row.unit_price"
                    required
                >
            </div>
            <input type="hidden" :name="'items['+index+'][discount]'" x-model="row.discount">
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700" x-bind:for="'quotation-item-tax-'+index"><?php echo e(__('Tax')); ?></label>
                <label class="flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-3">
                    <input
                        type="checkbox"
                        :id="'quotation-item-tax-'+index"
                        class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                        x-model="row.apply_tax"
                    >
                    <span class="text-xs text-slate-600" x-text="defaultTaxRate + '%'"></span>
                </label>
                <input type="hidden" :name="'items['+index+'][tax_rate]'" :value="effectiveTaxRate(row)">
            </div>
            <div class="min-w-0">
                <label class="mb-1 block text-xs font-semibold text-slate-700"><?php echo e(__('Total')); ?></label>
                <div class="flex h-10 items-center justify-end rounded-md border border-slate-200 bg-white px-3 text-sm font-medium tabular-nums text-slate-800" x-text="formatMoney(lineTotal(row))"></div>
            </div>
        </div>
    </template>

    <button
        type="button"
        class="erp-btn-secondary text-sm"
        @click="rows.push({item_type:'product',item_name:'',quantity:1,unit_price:0,discount:0,apply_tax:true})"
    ><?php echo e(__('Add line')); ?></button>

    <div class="flex justify-end border-t border-slate-200 pt-3">
        <dl class="w-full sm:w-72 space-y-1 text-sm">
            <div class="flex items-center justify-between font-semibold text-slate-800">
                <dt><?php echo e(__('Grand total')); ?></dt>
                <dd class="tabular-nums" x-text="formatMoney(grandTotal)"></dd>
            </div>
        </dl>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\quotations\partials\items-form.blade.php ENDPATH**/ ?>