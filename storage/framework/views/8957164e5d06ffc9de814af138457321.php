<?php
    use App\Enums\InventoryStockRole;
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedNotes = old('notes', $completion['suggested_notes'] ?? '');
?>

<dialog id="complete-fg-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="<?php echo e(route('admin.production.job-cards.outputs.store', $jobCard)); ?>" class="p-5">
        <?php echo csrf_field(); ?>
        <h3 class="text-base font-semibold text-slate-900"><?php echo e(__('Complete to finished goods')); ?></h3>
        <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Post completed output to the Finished Goods virtual warehouse. Accounting: Dr FG / Cr WIP (WIP was built from job material consumption).')); ?></p>

        <?php if(! ($completion['eligible'] ?? false) && ! empty($completion['blockers'] ?? [])): ?>
            <ul class="mt-3 space-y-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                <?php $__currentLoopData = $completion['blockers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($blocker); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>

        <div class="mt-4 space-y-3">
            <div>
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
                <?php if($finishedItems->isEmpty()): ?>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('No finished-good catalogue items yet. Create one in Inventory or update the sales order product stock role.')); ?></p>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label"><?php echo e(__('Quantity completed')); ?></label>
                    <input type="number" step="0.001" min="0.001" name="quantity_completed" class="erp-input w-full" value="<?php echo e($suggestedQty); ?>" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Quantity rejected')); ?></label>
                    <input type="number" step="0.001" min="0" name="quantity_rejected" class="erp-input w-full" value="<?php echo e(old('quantity_rejected', 0)); ?>">
                </div>
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
                <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                <textarea name="notes" rows="2" class="erp-input w-full"><?php echo e($suggestedNotes); ?></textarea>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" class="erp-btn-secondary" data-close-dialog="complete-fg-modal"><?php echo e(__('Cancel')); ?></button>
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Post to finished goods')); ?></button>
        </div>
    </form>
</dialog>

<script>
    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.openDialog);
            dialog?.showModal();
        });
    });
    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.closeDialog);
            dialog?.close();
        });
    });
</script>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/complete-finished-goods-modal.blade.php ENDPATH**/ ?>