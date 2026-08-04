<?php
    use App\Enums\InventoryStockRole;

    $eligible = (bool) ($completion['eligible'] ?? false);
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedNotes = old('notes', $completion['suggested_notes'] ?? '');
    $warehouseLabel = ($completion['fg_warehouse']['code'] ?? null)
        ? ($completion['fg_warehouse']['code'].' — '.$completion['fg_warehouse']['name'])
        : __('Finished goods virtual warehouse');
?>

<dialog id="complete-fg-modal" class="erp-modal job-360-fg-modal w-full max-w-xl rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="<?php echo e(route('admin.production.job-cards.outputs.store', $jobCard)); ?>" class="p-5">
        <?php echo csrf_field(); ?>
        <h3 class="text-base font-semibold text-slate-900"><?php echo e(__('Post finished goods')); ?></h3>
        <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Confirm output details before posting to finished goods inventory.')); ?></p>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="erp-label"><?php echo e(__('Finished item')); ?></label>
                <select name="finished_inventory_item_id" class="erp-select w-full" <?php if($suggestedId): ?> required <?php endif; ?>>
                    <?php if (! ($suggestedId)): ?>
                        <option value=""><?php echo e(__('Use BOM / sales order product')); ?></option>
                    <?php endif; ?>
                    <?php $__currentLoopData = $finishedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $needsRole = ($item->stock_role ?? null) !== InventoryStockRole::FinishedGood;
                        ?>
                        <option value="<?php echo e($item->id); ?>" <?php if((string) $suggestedId === (string) $item->id): echo 'selected'; endif; ?>>
                            <?php echo e($item->sku); ?> — <?php echo e($item->item_name); ?><?php if($needsRole): ?> (<?php echo e(__('set stock role to Finished Good')); ?>)<?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Completed qty')); ?></label>
                <input type="number" step="0.001" min="0.001" name="quantity_completed" class="erp-input w-full" value="<?php echo e($suggestedQty); ?>" required>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Rejected qty')); ?></label>
                <input type="number" step="0.001" min="0" name="quantity_rejected" class="erp-input w-full" value="<?php echo e(old('quantity_rejected', 0)); ?>">
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Unit cost')); ?></label>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.manual-cost')): ?>
                    <input type="number" step="0.0001" min="0" name="unit_cost" class="erp-input w-full" value="<?php echo e(old('unit_cost', $completion['suggested_unit_cost'] ?? '')); ?>" placeholder="<?php echo e(__('Leave blank to use job cost')); ?>">
                <?php else: ?>
                    <input type="text" class="erp-input w-full bg-slate-50" readonly value="<?php echo e(isset($completion['suggested_unit_cost']) ? number_format($completion['suggested_unit_cost'], 4) : __('Derived from job costing')); ?>">
                <?php endif; ?>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Warehouse')); ?></label>
                <input type="text" class="erp-input w-full bg-slate-50" readonly value="<?php echo e($warehouseLabel); ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                <textarea name="notes" rows="2" class="erp-input w-full"><?php echo e($suggestedNotes); ?></textarea>
            </div>
        </div>

        <?php if($eligible): ?>
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800"><?php echo e(__('Accounting impact')); ?></p>
                <div class="mt-1 grid gap-1 text-sm text-emerald-900 sm:grid-cols-2">
                    <p><?php echo e(__('Dr Finished goods inventory')); ?></p>
                    <p><?php echo e(__('Cr Work in progress')); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <button type="button" class="erp-btn-secondary" data-close-dialog="complete-fg-modal"><?php echo e(__('Cancel')); ?></button>
            <button type="submit" class="erp-btn-primary" <?php if(! $eligible): echo 'disabled'; endif; ?>><?php echo e(__('Post finished goods')); ?></button>
        </div>
    </form>
</dialog>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\complete-finished-goods-modal.blade.php ENDPATH**/ ?>