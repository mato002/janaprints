<?php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $hasFiles = $request->files->isNotEmpty();
?>

<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title"><?php echo e(__('Reference files')); ?></h2>

    <?php if($hasFiles): ?>
        <ul class="space-y-1 text-sm">
            <?php $__currentLoopData = $request->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="rounded bg-slate-50 px-2.5 py-1.5"><?php echo e($file->original_name); ?> <span class="text-slate-500">(<?php echo e($file->file_type->value); ?>)</span></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php else: ?>
        <div class="artwork-detail-empty py-6">
            <span class="artwork-detail-empty__icon" aria-hidden="true">📎</span>
            <p class="artwork-detail-empty__title"><?php echo e(__('No reference files')); ?></p>
            <p class="artwork-detail-empty__hint"><?php echo e(__('Upload customer briefs, logos, or other reference material.')); ?></p>
        </div>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $request)): ?>
        <div class="artwork-detail-upload-section">
            <p class="artwork-detail-upload-section__title"><?php echo e(__('Upload reference')); ?></p>
            <form
                method="POST"
                action="<?php echo e(route('admin.artwork.files.store', $request)); ?>"
                enctype="multipart/form-data"
                <?php if (! ($fromDesk)): ?>
                    data-turbo-frame="erp-main"
                <?php endif; ?>
                <?php if($fromDesk): ?>
                    data-erp-desk-form
                <?php endif; ?>
                class="space-y-3"
            >
                <?php echo csrf_field(); ?>
                <?php if($fromDesk): ?>
                    <input type="hidden" name="from" value="designer-desk">
                <?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal6384af2cfbb3fb249311eef9f601626b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6384af2cfbb3fb249311eef9f601626b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.file-upload','data' => ['name' => 'file','label' => __('Choose reference file'),'hint' => __('PDF, images, or design files'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.file-upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'file','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Choose reference file')),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PDF, images, or design files')),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6384af2cfbb3fb249311eef9f601626b)): ?>
<?php $attributes = $__attributesOriginal6384af2cfbb3fb249311eef9f601626b; ?>
<?php unset($__attributesOriginal6384af2cfbb3fb249311eef9f601626b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6384af2cfbb3fb249311eef9f601626b)): ?>
<?php $component = $__componentOriginal6384af2cfbb3fb249311eef9f601626b; ?>
<?php unset($__componentOriginal6384af2cfbb3fb249311eef9f601626b); ?>
<?php endif; ?>
                <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Upload reference')); ?></button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/partials/reference-files-panel.blade.php ENDPATH**/ ?>