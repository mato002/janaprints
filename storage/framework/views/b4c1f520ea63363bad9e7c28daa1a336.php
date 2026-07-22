<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Edit sales order'),'breadcrumbs' => [['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)], ['label' => __('Edit')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $salesOrder->order_number]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrder->order_number)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <form method="POST" action="<?php echo e(route('admin.sales-orders.update', $salesOrder)); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                <div>
                    <label class="erp-label"><?php echo e(__('Order date')); ?></label>
                    <input type="date" name="order_date" class="erp-input w-full" value="<?php echo e(old('order_date', $salesOrder->order_date->format('Y-m-d'))); ?>" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Required date')); ?></label>
                    <input type="date" name="required_date" class="erp-input w-full" value="<?php echo e(old('required_date', $salesOrder->required_date?->format('Y-m-d'))); ?>">
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Fulfilment method')); ?></label>
                    <select name="fulfilment_method" class="erp-input w-full">
                        <?php $__currentLoopData = \App\Enums\FulfilmentMethod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($method->value); ?>" <?php if(old('fulfilment_method', $salesOrder->fulfilment_method?->value ?? 'collection') === $method->value): echo 'selected'; endif; ?>>
                                <?php echo e($method->label()); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Billing type')); ?></label>
                    <select name="billing_type" class="erp-input w-full">
                        <?php $__currentLoopData = \App\Enums\SalesOrderBillingType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->value); ?>" <?php if(old('billing_type', $salesOrder->billing_type?->value ?? 'net_30') === $type->value): echo 'selected'; endif; ?>>
                                <?php echo e($type->label()); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Payment terms (days)')); ?></label>
                    <input type="number" name="payment_terms_days" class="erp-input w-full" min="0" max="365"
                        value="<?php echo e(old('payment_terms_days', $salesOrder->payment_terms_days ?? 30)); ?>">
                </div>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Notes')); ?></label>
                <textarea name="notes" class="erp-input w-full" rows="2"><?php echo e(old('notes', $salesOrder->notes)); ?></textarea>
            </div>

            <div class="rounded-lg border border-erp-border p-4 space-y-4" x-data="{ useArtwork: <?php echo \Illuminate\Support\Js::from((bool) old('uses_existing_artwork', $salesOrder->uses_existing_artwork))->toHtml() ?> }">
                <h3 class="font-medium"><?php echo e(__('Production product')); ?></h3>
                <div>
                    <label class="erp-label"><?php echo e(__('Catalogue item')); ?></label>
                    <select name="inventory_item_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('—')); ?></option>
                        <?php $__currentLoopData = $catalogueItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php if(old('inventory_item_id', $salesOrder->inventory_item_id) == $item->id): echo 'selected'; endif; ?>><?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <h3 class="font-medium"><?php echo e(__('Artwork')); ?></h3>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="uses_existing_artwork" value="1" x-model="useArtwork" <?php if(old('uses_existing_artwork', $salesOrder->uses_existing_artwork)): echo 'checked'; endif; ?>>
                    <span><?php echo e(__('Use existing artwork from customer library?')); ?></span>
                </label>
                <div x-show="useArtwork" x-cloak>
                    <label class="erp-label"><?php echo e(__('Artwork version')); ?></label>
                    <select name="customer_artwork_id" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select artwork')); ?></option>
                        <?php $__currentLoopData = $customerArtworks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($artwork->id); ?>" <?php if(old('customer_artwork_id', $salesOrder->customer_artwork_id) == $artwork->id): echo 'selected'; endif; ?>>
                                <?php echo e($artwork->artwork_name); ?> (v<?php echo e($artwork->version_number); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if($salesOrder->artwork_confirmed_at): ?>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Confirmed')); ?> <?php echo e($salesOrder->artwork_confirmed_at->format('Y-m-d H:i')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <h3 class="font-medium"><?php echo e(__('Line items')); ?></h3>
            <?php echo $__env->make('admin.sales.orders.partials.items-form', ['salesOrder' => $salesOrder], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Save changes')); ?></button>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\edit.blade.php ENDPATH**/ ?>