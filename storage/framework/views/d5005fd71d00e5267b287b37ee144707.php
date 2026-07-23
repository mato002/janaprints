<?php
    $editable = auth()->user()->can('update', $count);
?>

<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $count->count_number,'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($count->count_number),'maxWidth' => '5xl']); ?>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge"><?php echo e($count->status->value); ?></span>
            <span class="text-sm text-slate-600"><?php echo e($count->warehouse?->name); ?> · <?php echo e($count->count_date->format('d M Y')); ?></span>
        </div>

        <?php if($editable): ?>
            <form method="POST" action="<?php echo e(route('admin.inventory.stock-counts.worksheet.update', $count)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="from" value="store-desk">
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Item')); ?></th>
                                <th><?php echo e(__('System qty')); ?></th>
                                <th><?php echo e(__('Counted qty')); ?></th>
                                <th><?php echo e(__('Reason code')); ?></th>
                                <th><?php echo e(__('Comment')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $count->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php echo e($line->inventoryItem?->item_name); ?>

                                        <input type="hidden" name="items[<?php echo e($index); ?>][inventory_item_id]" value="<?php echo e($line->inventory_item_id); ?>">
                                    </td>
                                    <td><?php echo e($line->system_quantity); ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            name="items[<?php echo e($index); ?>][counted_quantity]"
                                            value="<?php echo e(old('items.'.$index.'.counted_quantity', $line->counted_quantity)); ?>"
                                            class="erp-input w-24"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <select name="items[<?php echo e($index); ?>][inventory_variance_reason_code_id]" class="erp-select w-full min-w-[8rem]">
                                            <option value=""><?php echo e(__('None')); ?></option>
                                            <?php $__currentLoopData = $reasonCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reasonCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($reasonCode->id); ?>" <?php if(old('items.'.$index.'.inventory_variance_reason_code_id', $line->inventory_variance_reason_code_id) == $reasonCode->id): echo 'selected'; endif; ?>>
                                                    <?php echo e($reasonCode->code); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="items[<?php echo e($index); ?>][reason]"
                                            value="<?php echo e(old('items.'.$index.'.reason', $line->reason)); ?>"
                                            class="erp-input w-full min-w-[8rem]"
                                            placeholder="<?php echo e(__('Explanation')); ?>"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Save worksheet')); ?></button>
                </div>
            </form>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg border border-erp-border">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Item')); ?></th>
                            <th><?php echo e(__('System')); ?></th>
                            <th><?php echo e(__('Counted')); ?></th>
                            <th><?php echo e(__('Variance')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $count->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($line->inventoryItem?->item_name); ?></td>
                                <td><?php echo e($line->system_quantity); ?></td>
                                <td><?php echo e($line->counted_quantity ?? '—'); ?></td>
                                <td><?php echo e($line->variance_quantity); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-2 border-t border-erp-border pt-3">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('submit', $count)): ?>
                <form method="POST" action="<?php echo e(route('admin.inventory.stock-counts.submit', $count)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Submit count')); ?></button>
                </form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $count)): ?>
                <form method="POST" action="<?php echo e(route('admin.inventory.stock-counts.approve', $count)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Approve')); ?></button>
                </form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('post', $count)): ?>
                <form method="POST" action="<?php echo e(route('admin.inventory.stock-counts.post', $count)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="from" value="store-desk">
                    <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Post variance')); ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\stock-count-modal.blade.php ENDPATH**/ ?>