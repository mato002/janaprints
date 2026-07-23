<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <h2 class="so-360__card-title"><?php echo e(__('Production product')); ?></h2>
        <?php if($salesOrder->inventoryItem): ?>
            <p class="mb-3 text-sm">
                <span class="font-semibold text-slate-900"><?php echo e($salesOrder->inventoryItem->item_name); ?></span>
                <span class="text-slate-500">(<?php echo e($salesOrder->inventoryItem->sku); ?>)</span>
            </p>
        <?php else: ?>
            <p class="mb-3 text-sm text-amber-700"><?php echo e(__('No catalogue product linked yet. Link a finished-good inventory item so production and material requirements can run.')); ?></p>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('updateProductionSetup', $salesOrder)): ?>
            <form method="POST" action="<?php echo e(route('admin.sales-orders.production-setup.update', $salesOrder)); ?>" class="flex flex-wrap items-end gap-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="min-w-[16rem] flex-1">
                    <label class="erp-label"><?php echo e(__('Catalogue item')); ?></label>
                    <select name="inventory_item_id" class="erp-input w-full" required>
                        <option value=""><?php echo e(__('Select product')); ?></option>
                        <?php $__currentLoopData = $catalogueItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php if($salesOrder->inventory_item_id == $item->id): echo 'selected'; endif; ?>>
                                <?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button type="submit" class="erp-btn-secondary"><?php echo e($salesOrder->inventoryItem ? __('Update product') : __('Link product')); ?></button>
            </form>
        <?php endif; ?>
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title"><?php echo e(__('Production links')); ?></h2>
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt><?php echo e(__('Artwork')); ?></dt>
                <dd>
                    <?php if($salesOrder->artworkRequest): ?>
                        <a href="<?php echo e(route('admin.artwork.show', $salesOrder->artworkRequest)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                            <?php echo e($salesOrder->artworkRequest->request_number); ?>

                        </a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt><?php echo e(__('Linked job card')); ?></dt>
                <dd>
                    <?php if($salesOrder->jobCard): ?>
                        <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="so-360__link font-mono" data-turbo-frame="erp-main">
                            <?php echo e($salesOrder->jobCard->job_card_number); ?>

                        </a>
                    <?php else: ?>
                        <?php echo e(__('Not created')); ?>

                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <?php if(($workflow['can_release'] ?? false)): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production', $salesOrder)): ?>
                <form method="POST" action="<?php echo e(route('admin.sales-orders.release-to-production', $salesOrder)); ?>" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Send to production')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </article>
</div>

<details class="so-360__collapse so-360__collapse--block mt-4" open>
    <summary><?php echo e(__('Line items & production specifications')); ?></summary>
    <div class="so-360__collapse-body">
        <?php $__empty_1 = true; $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('admin.sales.orders.partials.item-specification', [
                'salesOrder' => $salesOrder,
                'item' => $item,
                'itemSpecifications' => $itemSpecifications ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No line items.')); ?></p>
        <?php endif; ?>
    </div>
</details>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\workspace\tabs\production.blade.php ENDPATH**/ ?>