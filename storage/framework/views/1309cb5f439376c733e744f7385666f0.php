<?php
    $requirements = collect($tabData['material_requirements'] ?? []);
    $defaultRequirement = $requirements->first(fn ($row) => ($row['remaining'] ?? 0) > 0) ?? $requirements->first();
    $defaultItemId = old('inventory_item_id', $defaultRequirement['requirement']->inventory_item_id ?? null);
    $defaultWarehouseId = old('warehouse_id', $defaultRequirement['requirement']->warehouse_id ?? ($tabData['warehouses'][0]->id ?? null));
    $defaultQty = old('quantity', $defaultRequirement['remaining'] ?? null);
?>

<?php if($tabData['can_consume'] ?? false): ?>
    <dialog id="record-consumption-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="<?php echo e(route('admin.inventory.production.consume', $jobCard)); ?>" class="p-5">
            <?php echo csrf_field(); ?>
            <h3 class="text-base font-semibold text-slate-900"><?php echo e(__('Record consumption')); ?></h3>
            <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Deduct raw materials (paper, ink, etc.) from a physical warehouse — not the finished product.')); ?></p>

            <?php if($requirements->isEmpty()): ?>
                <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"><?php echo e(__('No material requirements on this job yet. Generate them from the Materials tab, or pick a raw material manually. Stock must exist in the warehouse first (Supply Chain → Direct Stock Receipts).')); ?></p>
            <?php elseif($requirements->contains(fn ($row) => ($row['remaining'] ?? 0) > 0)): ?>
                <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"><?php echo e(__('Prefer the Consume button on the Materials tab for each requirement line. Manual entry here is capped to the same remaining quantity.')); ?></p>
            <?php endif; ?>

            <div class="mt-4 grid grid-cols-1 gap-3">
                <div>
                    <label class="erp-label"><?php echo e(__('Raw material')); ?></label>
                    <select name="inventory_item_id" class="erp-select w-full" required>
                        <?php $__empty_1 = true; $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($inv->id); ?>" <?php if((string) $defaultItemId === (string) $inv->id): echo 'selected'; endif; ?>><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value=""><?php echo e(__('No raw materials found')); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Physical warehouse')); ?></label>
                    <select name="warehouse_id" class="erp-select w-full" required>
                        <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wh->id); ?>" <?php if((string) $defaultWarehouseId === (string) $wh->id): echo 'selected'; endif; ?>><?php echo e($wh->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Quantity')); ?></label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" value="<?php echo e($defaultQty); ?>" placeholder="<?php echo e(__('Qty')); ?>" required>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-consumption-modal"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Record consumption')); ?></button>
            </div>
        </form>
    </dialog>
<?php endif; ?>

<?php if($tabData['can_record_waste'] ?? false): ?>
    <dialog id="record-waste-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.wastage.store', $jobCard)); ?>" class="p-5">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="flow_type" value="waste">
            <h3 class="text-base font-semibold text-slate-900"><?php echo e(__('Record waste')); ?></h3>
            <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Capture production waste against this job for costing and yield tracking.')); ?></p>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Material')); ?></label>
                    <select name="inventory_item_id" class="erp-select w-full" required>
                        <?php $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($inv->id); ?>" <?php if((string) $defaultItemId === (string) $inv->id): echo 'selected'; endif; ?>><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Warehouse')); ?></label>
                    <select name="warehouse_id" class="erp-select w-full" required>
                        <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wh->id); ?>" <?php if((string) $defaultWarehouseId === (string) $wh->id): echo 'selected'; endif; ?>><?php echo e($wh->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Waste type')); ?></label>
                    <select name="waste_type" class="erp-select w-full" required>
                        <?php $__currentLoopData = \App\Enums\ProductionWasteType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Quantity')); ?></label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" value="<?php echo e(old('quantity', $defaultQty)); ?>" required>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-waste-modal"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Record waste')); ?></button>
            </div>
        </form>
    </dialog>

    <dialog id="record-return-modal" class="erp-modal w-full max-w-lg rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.wastage.store', $jobCard)); ?>" class="p-5">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="flow_type" value="return">
            <h3 class="text-base font-semibold text-slate-900"><?php echo e(__('Record material return')); ?></h3>
            <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Return unused material from the shop floor back to stock.')); ?></p>

            <div class="mt-4 grid grid-cols-1 gap-3">
                <div>
                    <label class="erp-label"><?php echo e(__('Material')); ?></label>
                    <select name="inventory_item_id" class="erp-select w-full" required>
                        <?php $__currentLoopData = $tabData['inventory_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($inv->id); ?>"><?php echo e($inv->sku); ?> — <?php echo e($inv->item_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Warehouse')); ?></label>
                    <select name="warehouse_id" class="erp-select w-full" required>
                        <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Quantity')); ?></label>
                    <input type="number" step="0.001" name="quantity" class="erp-input w-full" required>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="erp-btn-secondary" data-close-dialog="record-return-modal"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="erp-btn-secondary"><?php echo e(__('Record return')); ?></button>
            </div>
        </form>
    </dialog>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\material-consumption-modals.blade.php ENDPATH**/ ?>