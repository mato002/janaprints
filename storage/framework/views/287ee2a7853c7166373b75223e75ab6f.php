<?php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $uploadAnchor = $uploadAnchor ?? 'artwork-versions-upload';
    $hasVersions = $request->versions->isNotEmpty();
    $accept = '.pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg';
?>

<div class="artwork-detail-card" id="<?php echo e($uploadAnchor === 'artwork-versions-upload' ? 'designer-desk-versions' : $uploadAnchor); ?>">
    <h2 class="artwork-detail-card__title"><?php echo e(__('Versions')); ?></h2>

    <?php if($hasVersions): ?>
        <div class="mb-2">
            <?php $__currentLoopData = $request->versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="artwork-detail-version-row">
                    <div class="min-w-0">
                        <strong>v<?php echo e($version->version_number); ?></strong> — <?php echo e($version->original_name); ?>

                        <span class="text-slate-500">(<?php echo e($version->uploader?->name); ?>)</span>
                        <?php if($version->notes): ?>
                            <p class="mt-0.5 text-slate-600"><?php echo e($version->notes); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if($version->isPreviewable()): ?>
                        <button
                            type="button"
                            class="erp-btn-ghost shrink-0 text-xs"
                            data-preview-url="<?php echo e($version->previewUrl()); ?>"
                            data-preview-title="<?php echo e($version->original_name); ?>"
                            data-preview-pdf="<?php echo e($version->mime_type === 'application/pdf' ? '1' : '0'); ?>"
                            @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                        ><?php echo e(__('Preview')); ?></button>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="artwork-detail-empty">
            <span class="artwork-detail-empty__icon" aria-hidden="true">↑</span>
            <p class="artwork-detail-empty__title"><?php echo e(__('No versions yet')); ?></p>
            <p class="artwork-detail-empty__hint"><?php echo e(__('Upload the first artwork version to begin the approval workflow.')); ?></p>
        </div>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', [App\Models\Artwork\ArtworkVersion::class, $request])): ?>
        <div id="artwork-versions-upload" class="artwork-detail-upload-section">
            <p class="artwork-detail-upload-section__title"><?php echo e(__('Upload version')); ?></p>
            <form
                method="POST"
                action="<?php echo e(route('admin.artwork.versions.store', $request)); ?>"
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
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Artwork file')); ?></label>
                    <?php if (isset($component)) { $__componentOriginal6384af2cfbb3fb249311eef9f601626b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6384af2cfbb3fb249311eef9f601626b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.file-upload','data' => ['name' => 'file','accept' => $accept,'label' => __('Choose artwork file'),'hint' => __('PDF, AI, PSD, PNG, JPG…'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.file-upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'file','accept' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accept),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Choose artwork file')),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PDF, AI, PSD, PNG, JPG…')),'required' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Version notes')); ?></label>
                    <input type="text" name="notes" class="erp-input w-full text-sm" placeholder="<?php echo e(__('Optional notes for this version')); ?>">
                </div>
                <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['erp-btn-primary text-sm' => ! $hasVersions, 'erp-btn-secondary text-sm' => $hasVersions]); ?>"><?php echo e(__('Upload version')); ?></button>
            </form>
        </div>
    <?php else: ?>
        <?php if($request->lacksUploadedVersion()): ?>
            <p class="artwork-detail-upload-section mt-4 text-sm text-slate-500"><?php echo e(__('You do not have permission to upload artwork. Ask a designer or administrator to attach a file.')); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/partials/versions-panel.blade.php ENDPATH**/ ?>