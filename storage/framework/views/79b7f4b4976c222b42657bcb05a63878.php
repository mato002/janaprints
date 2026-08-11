<?php
    use App\Enums\FulfilmentMethod;
    use App\Enums\InventoryStockRole;
    use App\Enums\SalesOrderBillingType;
    use App\Support\Navigation\WorkspaceEmbed;

    $deskFrame = WorkspaceEmbed::turboFrame();
    $specArtwork = $specification->activeArtworkVersion;
    $specProduct = $specification->inventoryItem;
    $specArtworkRequired = $specProduct && $specProduct->stock_role === InventoryStockRole::FinishedGood;
    $specArtworkMissing = $specArtworkRequired && ! $specArtwork;
?>

<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Locked context')); ?></p>
                <p class="font-medium text-slate-900"><?php echo e($customer->name); ?></p>
                <p class="text-xs text-slate-600">
                    <?php echo e($specification->name); ?>

                    · <?php echo e($specification->specification_code); ?>

                    · <?php echo e($specProduct?->item_name); ?>

                </p>
                <p class="mt-1 text-xs">
                    <?php if($specArtwork): ?>
                        <span class="text-emerald-700">&#10003; <?php echo e(__('Artwork')); ?>: <?php echo e($specArtwork->versionLabel()); ?></span>
                    <?php elseif($specArtworkRequired): ?>
                        <span class="text-amber-700">! <?php echo e(__('Artwork required but missing')); ?></span>
                    <?php else: ?>
                        <span class="text-slate-400"><?php echo e(__('Artwork not required')); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a
                    href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]))); ?>"
                    class="erp-btn-secondary text-xs"
                    data-turbo-frame="<?php echo e($deskFrame); ?>"
                    data-turbo-action="advance"
                ><?php echo e(__('Change specification')); ?></a>
                <?php if(($deskUrls['customer_360'] ?? null)): ?>
                    <a href="<?php echo e($deskUrls['customer_360']); ?>" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main"><?php echo e(__('View Customer 360')); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($specArtworkMissing): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium"><?php echo e(__('Artwork needed before order')); ?></p>
            <p class="mt-1 text-xs"><?php echo e(__('Upload artwork on the specification, then continue. Order creation stays blocked until artwork is present.')); ?></p>
            <a
                class="erp-btn-secondary mt-2 inline-flex text-xs"
                href="<?php echo e(route('admin.crm.customers.print-specifications.edit', [$customer, $specification, 'from' => 'sales-desk'])); ?>"
                data-erp-modal-open
            ><?php echo e(__('Upload artwork')); ?></a>
        </div>
    <?php else: ?>
        <form
            method="POST"
            action="<?php echo e(route('admin.sales-orders.store')); ?>"
            class="space-y-3"
            data-turbo="false"
            data-erp-desk-form
            data-erp-desk-success-message="<?php echo e(__('Order created.')); ?>"
            data-erp-desk-submitting-message="<?php echo e(__('Creating order…')); ?>"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="from" value="sales-desk">
            <input type="hidden" name="entry_mode" value="direct">
            <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
            <input type="hidden" name="customer_print_specification_id" value="<?php echo e($specification->id); ?>">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="erp-label" for="desk-order-qty"><?php echo e(__('Order quantity')); ?></label>
                    <input id="desk-order-qty" type="number" name="quantity" class="erp-input w-full min-h-[2.75rem]" min="0.001" step="any" value="<?php echo e(old('quantity', $specification->default_quantity ?? 1)); ?>" required>
                    <?php if($specification->default_quantity): ?>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Pre-filled from specification default — change only if this order differs.')); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-price"><?php echo e(__('Order unit price')); ?></label>
                    <input id="desk-order-price" type="number" name="unit_price" class="erp-input w-full min-h-[2.75rem]" min="0" step="0.01" value="<?php echo e(old('unit_price', $specification->default_unit_price)); ?>">
                    <?php if($specification->default_unit_price !== null): ?>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Pre-filled from specification default — change only if this order differs.')); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-date"><?php echo e(__('Required date')); ?></label>
                    <input id="desk-order-date" type="date" name="required_date" class="erp-input w-full min-h-[2.75rem]" value="<?php echo e(old('required_date')); ?>">
                </div>
                <div>
                    <label class="erp-label" for="desk-order-priority"><?php echo e(__('Priority')); ?></label>
                    <select id="desk-order-priority" name="priority" class="erp-input w-full min-h-[2.75rem]">
                        <?php $__currentLoopData = $orderPriorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($priority->value); ?>" <?php if(old('priority', 'normal') === $priority->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($priority->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-fulfilment"><?php echo e(__('Fulfilment')); ?></label>
                    <select id="desk-order-fulfilment" name="fulfilment_method" class="erp-input w-full min-h-[2.75rem]">
                        <?php $__currentLoopData = FulfilmentMethod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($method->value); ?>" <?php if(old('fulfilment_method') === $method->value): echo 'selected'; endif; ?>><?php echo e($method->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-billing"><?php echo e(__('Billing type')); ?></label>
                    <select id="desk-order-billing" name="billing_type" class="erp-input w-full min-h-[2.75rem]">
                        <option value=""><?php echo e(__('Use customer default')); ?></option>
                        <?php $__currentLoopData = SalesOrderBillingType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->value); ?>" <?php if(old('billing_type') === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label" for="desk-order-notes"><?php echo e(__('Notes')); ?></label>
                    <textarea id="desk-order-notes" name="notes" class="erp-input w-full" rows="2"><?php echo e(old('notes')); ?></textarea>
                </div>
                <?php if($canSendToProduction ?? false): ?>
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="send_to_production" value="1" class="rounded border-erp-border" <?php if(old('send_to_production')): echo 'checked'; endif; ?>>
                            <?php echo e(__('Send to production')); ?>

                        </label>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Creates a production job card immediately. Leave unchecked to release from the next step.')); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-wrap justify-end gap-2 pt-1">
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Create order')); ?></button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/inline-order-form.blade.php ENDPATH**/ ?>