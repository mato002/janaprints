<?php
    $fields = $formFields ?? [];
    $dynamic = $dynamic ?? false;
    $directions = $directions ?? [];
    $defaultLine = ['inventory_item_id' => '', 'quantity' => '', 'unit_cost' => ''];

    if ($directions !== []) {
        $defaultLine['direction'] = $directions[0]->value ?? '';
    }

    $initialLines = collect(old('items', [$defaultLine]))
        ->values()
        ->map(function (array $line) use ($directions) {
            $mapped = [
                'inventory_item_id' => (string) ($line['inventory_item_id'] ?? ''),
                'quantity' => (string) ($line['quantity'] ?? ''),
                'unit_cost' => (string) ($line['unit_cost'] ?? ''),
            ];

            if ($directions !== []) {
                $mapped['direction'] = (string) ($line['direction'] ?? ($directions[0]->value ?? ''));
            }

            return $mapped;
        })
        ->all();

    if ($initialLines === []) {
        $initialLines = [$defaultLine];
    }

    $lineGridClass = $directions === []
        ? 'grid grid-cols-[minmax(0,1fr)_8rem_8rem_2rem] gap-2'
        : 'grid grid-cols-[minmax(0,1fr)_8rem_8rem_8rem_2rem] gap-2';
?>

<h3 class="font-medium mt-4"><?php echo e(__('Lines')); ?></h3>

<?php if($dynamic): ?>
    <div
        class="space-y-2"
        x-data="{
            lines: <?php echo \Illuminate\Support\Js::from($initialLines)->toHtml() ?>,
            addLine() {
                this.lines.push(<?php echo \Illuminate\Support\Js::from($defaultLine)->toHtml() ?>);
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                }
            },
        }"
    >
        <div class="<?php echo e($lineGridClass); ?> text-sm font-medium text-slate-500">
            <?php if(($fields['inventory_item_id']['visible'] ?? true)): ?><span><?php echo e($fields['inventory_item_id']['label'] ?? __('Item')); ?></span><?php endif; ?>
            <?php if(($fields['quantity']['visible'] ?? true)): ?><span><?php echo e($fields['quantity']['label'] ?? __('Qty')); ?></span><?php endif; ?>
            <?php if(($fields['unit_cost']['visible'] ?? true)): ?><span><?php echo e($fields['unit_cost']['label'] ?? __('Unit cost')); ?></span><?php endif; ?>
            <?php if($directions !== []): ?><span><?php echo e(__('Direction')); ?></span><?php endif; ?>
            <span class="sr-only"><?php echo e(__('Actions')); ?></span>
        </div>

        <template x-for="(line, index) in lines" :key="index">
            <div class="<?php echo e($lineGridClass); ?>">
                <?php if(($fields['inventory_item_id']['visible'] ?? true)): ?>
                    <select
                        :name="`items[${index}][inventory_item_id]`"
                        class="erp-input w-full min-w-0"
                        x-model="line.inventory_item_id"
                        <?php if($fields['inventory_item_id']['required'] ?? false): ?> required <?php endif; ?>
                    >
                        <option value=""><?php echo e(__('—')); ?></option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>"><?php echo e($item->sku); ?> — <?php echo e($item->item_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
                <?php if(($fields['quantity']['visible'] ?? true)): ?>
                    <input
                        type="number"
                        step="0.001"
                        min="0.001"
                        :name="`items[${index}][quantity]`"
                        class="erp-input w-full"
                        x-model="line.quantity"
                        placeholder="0"
                        <?php if($fields['quantity']['required'] ?? false): ?> required <?php endif; ?>
                    >
                <?php endif; ?>
                <?php if(($fields['unit_cost']['visible'] ?? true)): ?>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        :name="`items[${index}][unit_cost]`"
                        class="erp-input w-full"
                        x-model="line.unit_cost"
                        placeholder="0"
                        <?php if($fields['unit_cost']['required'] ?? false): ?> required <?php endif; ?>
                    >
                <?php endif; ?>
                <?php if($directions !== []): ?>
                    <select :name="`items[${index}][direction]`" class="erp-input w-full" x-model="line.direction">
                        <?php $__currentLoopData = $directions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d->value); ?>"><?php echo e($d->value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
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

        <button type="button" class="erp-btn-secondary text-xs" x-on:click="addLine()"><?php echo e(__('Add line')); ?></button>
    </div>
<?php else: ?>
    <div class="space-y-2">
        <div class="grid grid-cols-4 gap-2 text-sm font-medium text-slate-500">
            <?php if(($fields['inventory_item_id']['visible'] ?? true)): ?><span><?php echo e($fields['inventory_item_id']['label'] ?? __('Item')); ?></span><?php endif; ?>
            <?php if(($fields['quantity']['visible'] ?? true)): ?><span><?php echo e($fields['quantity']['label'] ?? __('Qty')); ?></span><?php endif; ?>
            <?php if(($fields['unit_cost']['visible'] ?? true)): ?><span><?php echo e($fields['unit_cost']['label'] ?? __('Unit cost')); ?></span><?php endif; ?>
            <?php if($directions !== []): ?><span><?php echo e(__('Direction')); ?></span><?php endif; ?>
        </div>
        <?php for($i = 0; $i < ($lineCount ?? 3); $i++): ?>
            <div class="grid grid-cols-4 gap-2">
                <?php if(($fields['inventory_item_id']['visible'] ?? true)): ?>
                    <select name="items[<?php echo e($i); ?>][inventory_item_id]" class="erp-input">
                        <option value=""><?php echo e(__('—')); ?></option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($item->id); ?>"><?php echo e($item->sku); ?> — <?php echo e($item->item_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
                <?php if(($fields['quantity']['visible'] ?? true)): ?>
                    <input type="number" step="0.001" min="0.001" name="items[<?php echo e($i); ?>][quantity]" class="erp-input" placeholder="0">
                <?php endif; ?>
                <?php if(($fields['unit_cost']['visible'] ?? true)): ?>
                    <input type="number" step="0.01" min="0" name="items[<?php echo e($i); ?>][unit_cost]" class="erp-input" placeholder="0">
                <?php endif; ?>
                <?php if($directions !== []): ?>
                    <select name="items[<?php echo e($i); ?>][direction]" class="erp-input">
                        <?php $__currentLoopData = $directions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->value); ?>"><?php echo e($d->value); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/inventory/partials/line-items.blade.php ENDPATH**/ ?>