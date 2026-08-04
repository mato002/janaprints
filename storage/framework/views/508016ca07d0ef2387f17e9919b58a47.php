<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $item->sku,'breadcrumbs' => [['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Items'), 'url' => route('admin.inventory.items.index')], ['label' => $item->sku]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $item->item_name,'description' => $item->sku]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->item_name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->sku)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if($item->stock_role): ?>
                <span class="erp-badge <?php echo e($item->stock_role->badgeClass()); ?>"><?php echo e($item->stock_role->label()); ?></span>
            <?php endif; ?>
            <span class="erp-badge"><?php echo e(__('Stock')); ?>: <?php echo e(number_format($stockBalance, 3)); ?></span>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $item)): ?><a href="<?php echo e(route('admin.inventory.items.edit', $item)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a><?php endif; ?>
         <?php $__env->endSlot(); ?>
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'xl:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'xl:col-span-2']); ?>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500"><?php echo e(__('Stock role')); ?></dt><dd><?php echo e($item->stock_role?->label() ?? __('-')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Category')); ?></dt><dd><?php echo e($item->category?->name); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Subcategory')); ?></dt><dd><?php echo e($item->subcategory?->name ?? __('-')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Brand')); ?></dt><dd><?php echo e($item->brand_name ?? $item->brand?->name ?? __('-')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Unit')); ?></dt><dd><?php echo e($item->unitOfMeasure?->name); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Reorder level')); ?></dt><dd><?php echo e($item->reorder_level); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Standard cost')); ?></dt><dd><?php echo e(number_format($item->standard_cost, 2)); ?></dd></div>
            </dl>

            <?php if($item->attributeValues->isNotEmpty()): ?>
                <h2 class="mt-6 text-sm font-semibold text-slate-900"><?php echo e(__('Attributes')); ?></h2>
                <dl class="mt-2 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    <?php $__currentLoopData = $item->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><dt class="text-slate-500"><?php echo e($value->attribute?->name); ?></dt><dd><?php echo e($value->option?->label ?? $value->value); ?></dd></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </dl>
            <?php endif; ?>

            <?php if($item->priceListItems->isNotEmpty()): ?>
                <h2 class="mt-6 text-sm font-semibold text-slate-900"><?php echo e(__('Prices')); ?></h2>
                <dl class="mt-2 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    <?php $__currentLoopData = $item->priceListItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><dt class="text-slate-500"><?php echo e($price->priceList?->name); ?></dt><dd><?php echo e($price->priceList?->currency); ?> <?php echo e(number_format((float) $price->price_override, 2)); ?></dd></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </dl>
            <?php endif; ?>

            <p class="text-xs text-slate-500 mt-4"><?php echo e(__('Balance is calculated from inventory movements only.')); ?></p>

            <?php if($item->uses_serial_numbers): ?>
                <h2 class="mt-6 text-sm font-semibold text-slate-900"><?php echo e(__('Serial format')); ?></h2>
                <p class="mt-1 text-sm"><code><?php echo e($item->serial_prefix); ?><?php echo e(str_repeat('0', max(0, ($item->serial_padding_length ?? 6) - 1))); ?>1</code></p>
            <?php endif; ?>

            <?php if($item->productionRouteSteps->isNotEmpty()): ?>
                <h2 class="mt-6 text-sm font-semibold text-slate-900"><?php echo e(__('Default production route')); ?></h2>
                <ol class="mt-2 list-decimal pl-5 text-sm space-y-1">
                    <?php $__currentLoopData = $item->productionRouteSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="<?php echo e($step->is_active ? '' : 'text-slate-400 line-through'); ?>"><?php echo e($step->step_name); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            <?php endif; ?>
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
            <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Product Images')); ?></h2>
            <form method="POST" enctype="multipart/form-data" action="<?php echo e(route('admin.inventory.items.images.store', $item)); ?>" class="mt-3 space-y-3">
                <?php echo csrf_field(); ?>
                <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="erp-input w-full">
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="primary" value="1"><span><?php echo e(__('Make primary')); ?></span></label>
                <button class="erp-btn-secondary"><?php echo e(__('Upload')); ?></button>
            </form>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <?php $__empty_1 = true; $__currentLoopData = $item->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-md border border-erp-border p-2">
                        <img src="<?php echo e($image->thumbnailUrl()); ?>" alt="" class="aspect-square w-full rounded object-cover">
                        <div class="mt-2 flex flex-wrap gap-1">
                            <?php if(! $image->is_primary): ?>
                                <form method="POST" action="<?php echo e(route('admin.inventory.items.images.primary', [$item, $image])); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="erp-btn-ghost py-1 text-xs"><?php echo e(__('Primary')); ?></button></form>
                            <?php else: ?>
                                <span class="erp-badge"><?php echo e(__('Primary')); ?></span>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('admin.inventory.items.images.destroy', [$item, $image])); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Remove this image?'))->toHtml() ?>)"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="erp-btn-ghost py-1 text-xs text-red-700"><?php echo e(__('Remove')); ?></button></form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'image','title' => __('No images uploaded')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'image','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No images uploaded'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
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
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\items\show.blade.php ENDPATH**/ ?>