<div id="order-items" class="space-y-3">
    <?php ($rows = old('items', $salesOrder->items->map(fn ($i) => [
        'item_name' => $i->item_name,
        'description' => $i->description,
        'quantity' => $i->quantity,
        'unit_price' => $i->unit_price,
    ])->toArray() ?: [['item_name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]])); ?>

    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 border-b pb-3">
            <input type="text" name="items[<?php echo e($index); ?>][item_name]" class="erp-input" placeholder="<?php echo e(__('Item')); ?>" value="<?php echo e($row['item_name'] ?? ''); ?>" required>
            <input type="text" name="items[<?php echo e($index); ?>][description]" class="erp-input" placeholder="<?php echo e(__('Description')); ?>" value="<?php echo e($row['description'] ?? ''); ?>">
            <input type="number" step="0.001" name="items[<?php echo e($index); ?>][quantity]" class="erp-input" value="<?php echo e($row['quantity'] ?? 1); ?>" required>
            <input type="number" step="0.01" name="items[<?php echo e($index); ?>][unit_price]" class="erp-input" value="<?php echo e($row['unit_price'] ?? 0); ?>" required>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\partials\items-form.blade.php ENDPATH**/ ?>