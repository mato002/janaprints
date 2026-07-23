<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Edit Media Slot'),'breadcrumbs' => [
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Media Library'), 'url' => route('admin.website.media.index')],
        ['label' => __('Edit')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $item->label ?? $item->slot_key,'description' => $usageLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->label ?? $item->slot_key),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($usageLabel)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <div class="flex flex-wrap items-center gap-3 p-4">
            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $sourceStatus['variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sourceStatus['variant'])]); ?><?php echo e($sourceStatus['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
            <span class="text-sm text-slate-600"><?php echo e($sourceStatus['detail']); ?></span>
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
        <form
            method="POST"
            action="<?php echo e(route('admin.website.media.update', $item)); ?>"
            enctype="multipart/form-data"
            class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-2"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="lg:col-span-2">
                <label class="erp-label"><?php echo e(__('Slot Key')); ?></label>
                <input type="text" class="erp-input bg-slate-50 font-mono text-sm" value="<?php echo e($item->slot_key); ?>" readonly disabled>
            </div>

            <div>
                <label class="erp-label" for="section"><?php echo e(__('Section')); ?></label>
                <input id="section" type="text" class="erp-input bg-slate-50" value="<?php echo e($sections[$item->section] ?? $item->section); ?>" readonly disabled>
            </div>

            <div>
                <label class="erp-label" for="label"><?php echo e(__('Admin Label')); ?></label>
                <input id="label" name="label" type="text" class="erp-input" value="<?php echo e(old('label', $item->label)); ?>">
                <?php $__errorArgs = ['label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="alt_text"><?php echo e(__('Image Alt Text')); ?> <span class="text-red-500">*</span></label>
                <input id="alt_text" name="alt_text" type="text" class="erp-input" value="<?php echo e(old('alt_text', $item->alt_text)); ?>" required>
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Required for accessibility and SEO on the public storefront.')); ?></p>
                <?php $__errorArgs = ['alt_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="erp-label" for="sort_order"><?php echo e(__('Sort Order')); ?></label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="erp-input" value="<?php echo e(old('sort_order', $item->sort_order)); ?>">
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" <?php if(old('is_active', $item->is_active)): echo 'checked'; endif; ?>>
                    <?php echo e(__('Active on storefront')); ?>

                </label>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="image"><?php echo e(__('Replace Image')); ?></label>
                <div class="mt-2 flex flex-wrap items-start gap-4">
                    <div class="overflow-hidden rounded-lg border border-slate-200">
                        <img
                            src="<?php echo e($item->previewUrl()); ?>"
                            alt="<?php echo e($item->alt_text); ?>"
                            class="h-32 w-48 object-cover"
                            onerror="this.onerror=null;this.src='<?php echo e(asset('images/storefront/facility/production-floor.jpg')); ?>';"
                        >
                    </div>
                    <div class="min-w-0 flex-1">
                        <input id="image" name="image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="erp-input">
                        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('JPG, PNG or WebP. Max 5 MB. Stored in website-media/ on the public disk.')); ?></p>
                        <?php if($item->fallback_path): ?>
                            <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Config fallback:')); ?> <code class="text-xs"><?php echo e($item->fallback_path); ?></code></p>
                        <?php endif; ?>
                        <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Save Media Slot')); ?></button>
                <a href="<?php echo e(route('admin.website.media.index', ['section' => $item->section])); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
                <?php if($item->hasUploadedImage()): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $item)): ?>
                        <button
                            type="submit"
                            form="remove-media-image"
                            class="erp-btn-secondary text-red-600"
                            onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('Remove uploaded image and revert to config fallback?'))->toHtml() ?>)"
                        >
                            <?php echo e(__('Remove Uploaded Image')); ?>

                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>

        <?php if($item->hasUploadedImage()): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $item)): ?>
                <form id="remove-media-image" method="POST" action="<?php echo e(route('admin.website.media.remove-image', $item)); ?>" class="hidden">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <input type="hidden" name="confirm" value="1">
                </form>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\website\media\form.blade.php ENDPATH**/ ?>