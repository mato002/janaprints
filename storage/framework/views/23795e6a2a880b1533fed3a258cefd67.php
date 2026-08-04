<?php
    $isEdit = $item->exists;
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $isEdit ? __('Edit Gallery Item') : __('Add Gallery Item'),'breadcrumbs' => [
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Website Content'), 'url' => route('admin.workspaces.administration.section', 'website-content')],
        ['label' => __('Gallery'), 'url' => route('admin.website.gallery.index')],
        ['label' => $isEdit ? __('Edit') : __('Create')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.website.partials.role-guidance', ['context' => 'gallery'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $isEdit ? __('Edit Gallery Item') : __('Add Gallery Item'),'description' => __('Upload project imagery and details for the public storefront gallery.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? __('Edit Gallery Item') : __('Add Gallery Item')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Upload project imagery and details for the public storefront gallery.'))]); ?>
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
        <form
            method="POST"
            action="<?php echo e($isEdit ? route('admin.website.gallery.update', $item) : route('admin.website.gallery.store')); ?>"
            enctype="multipart/form-data"
            class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-2"
        >
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <div class="lg:col-span-2">
                <label class="erp-label" for="title"><?php echo e(__('Title')); ?></label>
                <input id="title" name="title" type="text" class="erp-input" value="<?php echo e(old('title', $item->title)); ?>" required>
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="erp-label" for="category"><?php echo e(__('Category')); ?></label>
                <select id="category" name="category" class="erp-input" required>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(old('category', $item->category) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="erp-label" for="location"><?php echo e(__('Location')); ?></label>
                <input id="location" name="location" type="text" class="erp-input" value="<?php echo e(old('location', $item->location)); ?>" placeholder="<?php echo e(__('e.g. Nairobi')); ?>">
            </div>

            <div>
                <label class="erp-label" for="quantity_label"><?php echo e(__('Quantity / Project Size')); ?></label>
                <input id="quantity_label" name="quantity_label" type="text" class="erp-input" value="<?php echo e(old('quantity_label', $item->quantity_label)); ?>" placeholder="<?php echo e(__('e.g. 2,500 business cards')); ?>">
            </div>

            <div>
                <label class="erp-label" for="timeline_label"><?php echo e(__('Completion Timeline')); ?></label>
                <input id="timeline_label" name="timeline_label" type="text" class="erp-input" value="<?php echo e(old('timeline_label', $item->timeline_label)); ?>" placeholder="<?php echo e(__('e.g. 3 business days')); ?>">
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="materials_label"><?php echo e(__('Materials Used')); ?></label>
                <input id="materials_label" name="materials_label" type="text" class="erp-input" value="<?php echo e(old('materials_label', $item->materials_label)); ?>" placeholder="<?php echo e(__('e.g. 400gsm black core card, soft-touch laminate')); ?>">
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="description"><?php echo e(__('Description')); ?></label>
                <textarea id="description" name="description" rows="4" class="erp-input"><?php echo e(old('description', $item->description)); ?></textarea>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="outcome"><?php echo e(__('Outcome')); ?></label>
                <textarea id="outcome" name="outcome" rows="3" class="erp-input" placeholder="<?php echo e(__('e.g. Delivered ahead of the client launch event.')); ?>"><?php echo e(old('outcome', $item->outcome)); ?></textarea>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="alt_text"><?php echo e(__('Image Alt Text')); ?></label>
                <input id="alt_text" name="alt_text" type="text" class="erp-input" value="<?php echo e(old('alt_text', $item->alt_text)); ?>" required>
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
                <input id="sort_order" name="sort_order" type="number" min="0" class="erp-input" value="<?php echo e(old('sort_order', $item->sort_order)); ?>">
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Lower numbers appear first among featured items.')); ?></p>
            </div>

            <div class="flex flex-col gap-3">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $item)): ?>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" <?php if(old('is_featured', $item->is_featured)): echo 'checked'; endif; ?>>
                        <?php echo e(__('Featured on homepage')); ?>

                    </label>
                <?php endif; ?>
                <?php if(auth()->user()->can('website.gallery.publish')): ?>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300" <?php if(old('is_published', $item->is_published ?? true)): echo 'checked'; endif; ?>>
                        <?php echo e(__('Published on public site')); ?>

                    </label>
                <?php else: ?>
                    <input type="hidden" name="is_published" value="<?php echo e(old('is_published', $item->is_published ?? false) ? '1' : '0'); ?>">
                    <p class="text-xs text-slate-500">
                        <?php echo e($item->is_published ? __('Published on public site') : __('Hidden from public site')); ?>

                        — <?php echo e(__('Publish permission required to change visibility.')); ?>

                    </p>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2">
                <label class="erp-label" for="image"><?php echo e($isEdit ? __('Replace Image') : __('Project Image')); ?></label>
                <?php if($isEdit && $item->image_path): ?>
                    <div class="mb-3">
                        <img src="<?php echo e($item->publicImageUrl()); ?>" alt="<?php echo e($item->alt_text); ?>" class="h-32 w-48 rounded-lg object-cover">
                    </div>
                <?php endif; ?>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="erp-input" <?php if (! ($isEdit)): ?> required <?php endif; ?>>
                <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="lg:col-span-2 flex gap-3">
                <button type="submit" class="erp-btn-primary"><?php echo e($isEdit ? __('Save Changes') : __('Create Gallery Item')); ?></button>
                <a href="<?php echo e(route('admin.website.gallery.index')); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\website\gallery\form.blade.php ENDPATH**/ ?>