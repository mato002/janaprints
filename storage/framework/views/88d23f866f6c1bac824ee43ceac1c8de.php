<?php
    $classifications = $classifications ?? \App\Enums\ProcurementItemClassification::cases();
    $assetCategories = $assetCategories ?? collect();
    $costField = $mode === 'request' ? 'estimated_unit_cost' : 'unit_cost';
    $costLabel = $mode === 'request' ? __('Est. unit cost') : __('Unit cost');

    $defaultLine = [
        'inventory_item_id' => '',
        'item_classification' => 'inventory_item',
        'asset_category_id' => '',
        'description' => '',
        'quantity' => '1',
        $costField => '0',
    ];

    $initialLines = collect(old('items', ($existing ?? collect())->map(fn ($line) => [
        'inventory_item_id' => (string) ($line->inventory_item_id ?? ''),
        'item_classification' => $line->item_classification?->value ?? $line->item_classification ?? 'inventory_item',
        'asset_category_id' => (string) ($line->asset_category_id ?? ''),
        'description' => $line->description ?? '',
        'quantity' => (string) ($line->quantity ?? 1),
        $costField => (string) ($line->{$costField} ?? $line->unit_cost ?? $line->estimated_unit_cost ?? 0),
    ])->values()->all() ?: [$defaultLine]))
        ->values()
        ->map(function (array $line) use ($defaultLine, $costField) {
            return [
                'inventory_item_id' => (string) ($line['inventory_item_id'] ?? ''),
                'item_classification' => (string) ($line['item_classification'] ?? $defaultLine['item_classification']),
                'asset_category_id' => (string) ($line['asset_category_id'] ?? ''),
                'description' => (string) ($line['description'] ?? ''),
                'quantity' => (string) ($line['quantity'] ?? '1'),
                $costField => (string) ($line[$costField] ?? $line['unit_cost'] ?? $line['estimated_unit_cost'] ?? '0'),
            ];
        })
        ->all();

    if ($initialLines === []) {
        $initialLines = [$defaultLine];
    }

    $lineGridClass = 'grid min-w-[56rem] grid-cols-[7.5rem_minmax(0,1.1fr)_minmax(0,7.5rem)_minmax(0,1.2fr)_5.5rem_5.5rem_2rem] gap-2';
?>

<div>
    <h3 class="mb-2 text-sm font-semibold"><?php echo e(__('Line items')); ?></h3>
    <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Add every product or service on this order. Pick a catalogue item when ordering stock you already track in inventory.')); ?></p>

    <div
        class="space-y-2 overflow-x-auto"
        x-data="{
            lines: <?php echo \Illuminate\Support\Js::from($initialLines)->toHtml() ?>,
            costField: <?php echo \Illuminate\Support\Js::from($costField)->toHtml() ?>,
            defaultLine: <?php echo \Illuminate\Support\Js::from($defaultLine)->toHtml() ?>,
            addLine() {
                this.lines.push({ ...this.defaultLine });
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                }
            },
        }"
    >
        <div class="<?php echo e($lineGridClass); ?> text-xs font-medium text-slate-500">
            <span><?php echo e(__('Type')); ?></span>
            <span><?php echo e(__('Catalogue item')); ?></span>
            <span><?php echo e(__('Asset category')); ?></span>
            <span><?php echo e(__('Description')); ?></span>
            <span><?php echo e(__('Qty')); ?></span>
            <span><?php echo e($costLabel); ?></span>
            <span class="sr-only"><?php echo e(__('Actions')); ?></span>
        </div>

        <template x-for="(line, index) in lines" :key="index">
            <div class="<?php echo e($lineGridClass); ?> items-center">
                <select
                    :name="`items[${index}][item_classification]`"
                    class="erp-select w-full min-w-0 text-sm"
                    x-model="line.item_classification"
                    required
                >
                    <?php $__currentLoopData = $classifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($classification->value); ?>"><?php echo e($classification->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select
                    :name="`items[${index}][inventory_item_id]`"
                    class="erp-select w-full min-w-0 text-sm"
                    x-model="line.inventory_item_id"
                    @change="if ($event.target.selectedOptions[0]?.dataset?.label && ! line.description) line.description = $event.target.selectedOptions[0].dataset.label"
                >
                    <option value=""><?php echo e(__('None — manual description')); ?></option>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>" data-label="<?php echo e($item->item_name); ?>"><?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select
                    :name="`items[${index}][asset_category_id]`"
                    class="erp-select w-full min-w-0 text-sm"
                    x-model="line.asset_category_id"
                >
                    <option value=""><?php echo e(__('None')); ?></option>
                    <?php $__currentLoopData = $assetCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <input
                    :name="`items[${index}][description]`"
                    x-model="line.description"
                    class="erp-input w-full min-w-0 text-sm"
                    placeholder="<?php echo e(__('What you are buying')); ?>"
                    required
                >

                <input
                    :name="`items[${index}][quantity]`"
                    type="number"
                    step="0.001"
                    min="0.001"
                    x-model="line.quantity"
                    class="erp-input w-full text-sm"
                    required
                >

                <input
                    :name="`items[${index}][${costField}]`"
                    type="number"
                    step="0.01"
                    min="0"
                    x-model="line[costField]"
                    class="erp-input w-full text-sm"
                    required
                >

                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm text-rose-600 hover:bg-rose-50"
                        x-on:click="removeLine(index)"
                        x-show="lines.length > 1"
                        :title="<?php echo \Illuminate\Support\Js::from(__('Remove line'))->toHtml() ?>"
                    >
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only"><?php echo e(__('Remove line')); ?></span>
                    </button>
                </div>
            </div>
        </template>

        <button type="button" class="erp-btn-secondary text-sm" x-on:click="addLine()"><?php echo e(__('Add line')); ?></button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\partials\line-items-form.blade.php ENDPATH**/ ?>