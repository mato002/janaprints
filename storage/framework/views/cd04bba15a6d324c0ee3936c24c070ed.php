<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $title,'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Management'), 'url' => route('admin.assets.index')],
        ['label' => $title],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title)]); ?>
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
        <form method="POST" action="<?php echo e($action); ?>" class="max-w-xl space-y-4">
            <?php echo csrf_field(); ?>
            <?php if($method !== 'POST'): ?>
                <?php echo method_field($method); ?>
            <?php endif; ?>
            <div>
                <label class="text-xs text-slate-600" for="name"><?php echo e(__('Name')); ?></label>
                <input id="name" name="name" class="erp-input mt-1 w-full" value="<?php echo e(old('name', $category?->name)); ?>" required>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="code"><?php echo e(__('Code')); ?></label>
                <input id="code" name="code" class="erp-input mt-1 w-full" value="<?php echo e(old('code', $category?->code)); ?>">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="asset_type"><?php echo e(__('Asset Type')); ?></label>
                <select id="asset_type" name="asset_type" class="erp-select mt-1 w-full" required>
                    <?php $__currentLoopData = $assetTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>" <?php if(old('asset_type', $category?->asset_type?->value) === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="useful_life_years"><?php echo e(__('Useful Life (years)')); ?></label>
                <input id="useful_life_years" type="number" min="1" max="100" name="useful_life_years" class="erp-input mt-1 w-full" value="<?php echo e(old('useful_life_years', $category?->useful_life_years ?? 5)); ?>" required>
            </div>
            <div>
                <label class="text-xs text-slate-600" for="default_gl_code"><?php echo e(__('Default GL Code')); ?></label>
                <input id="default_gl_code" name="default_gl_code" class="erp-input mt-1 w-full" value="<?php echo e(old('default_gl_code', $category?->default_gl_code)); ?>">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="depreciation_method"><?php echo e(__('Depreciation Method')); ?></label>
                <input id="depreciation_method" name="depreciation_method" class="erp-input mt-1 w-full" value="<?php echo e(old('depreciation_method', $category?->depreciation_method ?? 'straight_line')); ?>">
            </div>
            <div>
                <label class="text-xs text-slate-600" for="description"><?php echo e(__('Description')); ?></label>
                <textarea id="description" name="description" rows="3" class="erp-input mt-1 w-full"><?php echo e(old('description', $category?->description)); ?></textarea>
            </div>
            <?php if($category): ?>
                <div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $category->is_active)): echo 'checked'; endif; ?>>
                        <?php echo e(__('Active')); ?>

                    </label>
                </div>
            <?php endif; ?>
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary"><?php echo e(__('Save')); ?></button>
                <a href="<?php echo e(route('admin.assets.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\categories\partials\form.blade.php ENDPATH**/ ?>