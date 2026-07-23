<?php
    $focusPanel = $focusPanel ?? request('panel');
?>

<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $request->request_number,'maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->request_number),'maxWidth' => '5xl']); ?>
    <?php if (isset($component)) { $__componentOriginal0de0e52b643095bf2659e655794f27e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0de0e52b643095bf2659e655794f27e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-preview-lightbox','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-preview-lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div
            class="space-y-4"
            <?php if($focusPanel === 'versions'): ?>
                x-data
                x-init="$nextTick(() => document.getElementById('designer-desk-versions')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
            <?php endif; ?>
        >
            <div class="flex flex-wrap items-center gap-2 border-b border-erp-border pb-3">
                <span class="erp-badge"><?php echo e(str_replace('_', ' ', $request->status->value)); ?></span>
                <span class="text-sm text-slate-500">v<?php echo e($request->current_version ?: '0'); ?></span>
                <span class="text-sm font-medium text-slate-700"><?php echo e($request->title); ?></span>
                <?php if($request->customer?->company_name): ?>
                    <span class="text-sm text-slate-500">— <?php echo e($request->customer->company_name); ?></span>
                <?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginalf5ffa9581a76bd6f6146407ee4444540 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workflow-error','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workflow-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $attributes = $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $component = $__componentOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>

            <?php if($request->status === \App\Enums\ArtworkRequestStatus::Requested): ?>
                <p class="text-sm text-slate-600"><?php echo e(__('Start design, then upload a version before submitting for approval.')); ?></p>
            <?php elseif($request->status === \App\Enums\ArtworkRequestStatus::InDesign && $request->lacksUploadedVersion()): ?>
                <p class="text-sm text-slate-600"><?php echo e(__('Upload at least one artwork version before submitting for approval.')); ?></p>
            <?php elseif($request->lacksUploadedVersion() && $request->status->isEditable()): ?>
                <p class="text-sm text-amber-700"><?php echo e(__('Upload a version below to continue.')); ?></p>
            <?php endif; ?>

            <div class="flex flex-wrap gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('startDesign', $request)): ?>
                    <form method="POST" action="<?php echo e(route('admin.artwork.start-design', $request)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="from" value="designer-desk">
                        <button type="submit" class="erp-btn-secondary text-sm">
                            <?php echo e($request->status === \App\Enums\ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design')); ?>

                        </button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('submit', $request)): ?>
                    <form method="POST" action="<?php echo e(route('admin.artwork.submit', $request)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="from" value="designer-desk">
                        <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Submit for approval')); ?></button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Details')); ?></h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt>
                            <dd><?php echo e(ucfirst($request->priority->value)); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500"><?php echo e(__('Due')); ?></dt>
                            <dd><?php echo e($request->due_date?->format('d M Y') ?? '—'); ?></dd>
                        </div>
                        <?php if($request->description): ?>
                            <div>
                                <dt class="text-slate-500"><?php echo e(__('Description')); ?></dt>
                                <dd class="mt-1 text-slate-700"><?php echo e($request->description); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Reference files')); ?></h3>
                    <?php $__empty_1 = true; $__currentLoopData = $request->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="py-1 text-sm"><?php echo e($file->original_name); ?> (<?php echo e($file->file_type->value); ?>)</div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-500"><?php echo e(__('No reference files.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="designer-desk-versions" class="rounded-lg border border-erp-border bg-white p-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Versions')); ?></h3>
                <?php $__empty_1 = true; $__currentLoopData = $request->versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0">
                        <div>
                            <strong>v<?php echo e($version->version_number); ?></strong> — <?php echo e($version->original_name); ?>

                            <span class="text-slate-500">(<?php echo e($version->uploader?->name); ?>)</span>
                            <?php if($version->notes): ?>
                                <p class="text-slate-600"><?php echo e($version->notes); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if($version->isPreviewable()): ?>
                            <button
                                type="button"
                                class="erp-btn-ghost text-xs"
                                data-preview-url="<?php echo e($version->previewUrl()); ?>"
                                data-preview-title="<?php echo e($version->original_name); ?>"
                                data-preview-pdf="<?php echo e($version->mime_type === 'application/pdf' ? '1' : '0'); ?>"
                                @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                            ><?php echo e(__('Preview')); ?></button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-500"><?php echo e(__('No versions uploaded yet.')); ?></p>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', [App\Models\Artwork\ArtworkVersion::class, $request])): ?>
                    <form
                        method="POST"
                        action="<?php echo e(route('admin.artwork.versions.store', $request)); ?>"
                        enctype="multipart/form-data"
                        class="mt-4 space-y-2 border-t border-erp-border pt-4"
                    >
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="from" value="designer-desk">
                        <label class="block text-xs font-semibold text-slate-700"><?php echo e(__('Artwork file')); ?></label>
                        <input type="file" name="file" class="erp-input w-full" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                        <label class="block text-xs font-semibold text-slate-700"><?php echo e(__('Version notes')); ?></label>
                        <input type="text" name="notes" class="erp-input w-full" placeholder="<?php echo e(__('Optional notes for this version')); ?>">
                        <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Upload version')); ?></button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if($request->comments->isNotEmpty() || $request->approvals->isNotEmpty()): ?>
                <div class="rounded-lg border border-erp-border bg-white p-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Comments & approvals')); ?></h3>
                    <?php $__currentLoopData = $request->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                            <span class="erp-badge"><?php echo e($comment->comment_type->value); ?></span>
                            <?php echo e($comment->user?->name); ?>: <?php echo e($comment->comment); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $request->approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mt-2 text-sm text-slate-600">
                            <?php echo e($approval->decision->value); ?> — <?php echo e($approval->approver?->name); ?>

                            <?php if($approval->comments): ?> (<?php echo e($approval->comments); ?>) <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.comments.store', $request)); ?>" class="space-y-2 rounded-lg border border-erp-border bg-slate-50 p-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="from" value="designer-desk">
                    <label class="block text-xs font-semibold text-slate-700"><?php echo e(__('Add comment')); ?></label>
                    <select name="comment_type" class="erp-input w-full">
                        <option value="internal"><?php echo e(__('Internal')); ?></option>
                        <option value="customer"><?php echo e(__('Customer')); ?></option>
                    </select>
                    <textarea name="comment" class="erp-input w-full" rows="2" required placeholder="<?php echo e(__('Notes for the team…')); ?>"></textarea>
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Save comment')); ?></button>
                </form>
            <?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $attributes = $__attributesOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__attributesOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $component = $__componentOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__componentOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\desk\request-modal.blade.php ENDPATH**/ ?>