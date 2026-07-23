<?php
    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $artworkHomeLabel = $designerOperator ? __('Designer Desk') : __('Artwork');
    $artworkHomeUrl = $designerOperator
        ? route('admin.artwork.desk')
        : route('admin.artwork.dashboard');
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $request->request_number,'breadcrumbs' => [['label' => $artworkHomeLabel, 'url' => $artworkHomeUrl], ['label' => $request->request_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $request->request_number,'description' => $request->customer?->company_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->request_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->customer?->company_name)]); ?>
        <?php if($designerOperator): ?>
            <a href="<?php echo e(route('admin.artwork.desk')); ?>" class="erp-btn-secondary" data-turbo-frame="erp-main"><?php echo e(__('Back to Designer Desk')); ?></a>
        <?php endif; ?>
        <span class="erp-badge"><?php echo e(str_replace('_', ' ', $request->status->value)); ?></span>
        <span class="text-sm text-slate-500">v<?php echo e($request->current_version); ?></span>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $request)): ?>
            <a href="<?php echo e(route('admin.artwork.edit', $request)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
        <?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <h3 class="font-medium mb-3"><?php echo e(__('Workflow')); ?></h3>
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
            <p class="mb-3 text-sm text-slate-600"><?php echo e(__('Assign a designer, start design, or upload a version before submitting for approval.')); ?></p>
        <?php elseif($request->status === \App\Enums\ArtworkRequestStatus::InDesign && $request->lacksUploadedVersion()): ?>
            <p class="mb-3 text-sm text-slate-600"><?php echo e(__('Upload at least one artwork version before submitting for approval.')); ?></p>
        <?php elseif($request->lacksUploadedVersion()): ?>
            <p class="mb-3 text-sm text-amber-700"><?php echo e(__('No artwork file is attached yet. Upload a version below to unblock this request.')); ?></p>
        <?php endif; ?>
        <div class="flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.assign', $request)); ?>" class="flex flex-wrap gap-2 items-end">
                    <?php echo csrf_field(); ?>
                    <select name="assigned_designer_id" class="erp-input" required>
                        <option value=""><?php echo e(__('Assign designer')); ?></option>
                        <?php $__currentLoopData = \App\Models\User::query()->where('company_id', $request->company_id)->where('is_active', true)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($designer->id); ?>" <?php if($request->assigned_designer_id === $designer->id): echo 'selected'; endif; ?>><?php echo e($designer->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="erp-btn-secondary"><?php echo e(__('Assign')); ?></button>
                </form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('submit', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.submit', $request)); ?>"><?php echo csrf_field(); ?>
                    <button class="erp-btn-primary"><?php echo e(__('Submit for approval')); ?></button></form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('startDesign', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.start-design', $request)); ?>"><?php echo csrf_field(); ?>
                    <button class="erp-btn-secondary">
                        <?php echo e($request->status === \App\Enums\ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design')); ?>

                    </button></form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $request)): ?>
                <?php if($request->status === \App\Enums\ArtworkRequestStatus::Submitted && $request->lacksUploadedVersion()): ?>
                    <form method="POST" action="<?php echo e(route('admin.artwork.approve', $request)); ?>" class="flex flex-wrap gap-2 items-end">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="decision" value="rejected">
                        <input type="text" name="comments" class="erp-input" placeholder="<?php echo e(__('Rejection reason')); ?>">
                        <button class="erp-btn-secondary"><?php echo e(__('Reject request')); ?></button>
                    </form>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('startDesign', $request)): ?>
                        <form method="POST" action="<?php echo e(route('admin.artwork.start-design', $request)); ?>"><?php echo csrf_field(); ?>
                            <button class="erp-btn-primary"><?php echo e(__('Return to design')); ?></button>
                        </form>
                    <?php endif; ?>
                <?php elseif($request->canApproveOrRequestRevision()): ?>
                    <form method="POST" action="<?php echo e(route('admin.artwork.approve', $request)); ?>" class="flex flex-wrap gap-2 items-end">
                        <?php echo csrf_field(); ?>
                        <select name="decision" class="erp-input" required>
                            <option value="approved"><?php echo e(__('Approve')); ?></option>
                            <option value="revision_requested"><?php echo e(__('Request revision')); ?></option>
                            <option value="rejected"><?php echo e(__('Reject')); ?></option>
                        </select>
                        <input type="text" name="comments" class="erp-input" placeholder="<?php echo e(__('Comments')); ?>">
                        <button class="erp-btn-primary"><?php echo e(__('Record decision')); ?></button>
                    </form>
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
            <h3 class="font-medium mb-3"><?php echo e(__('Details')); ?></h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500"><?php echo e(__('Title')); ?></dt><dd><?php echo e($request->title); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt><dd><?php echo e($request->priority->value); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Due')); ?></dt><dd><?php echo e($request->due_date?->format('Y-m-d') ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Designer')); ?></dt><dd><?php echo e($request->assignedDesigner?->name ?? '—'); ?></dd></div>
                <?php if($request->description): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Description')); ?></dt><dd><?php echo e($request->description); ?></dd></div>
                <?php endif; ?>
            </dl>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Versions')); ?></h3>
            <?php $__empty_1 = true; $__currentLoopData = $request->versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 py-2 text-sm">
                    <div>
                        <strong>v<?php echo e($version->version_number); ?></strong> — <?php echo e($version->original_name); ?>

                        <span class="text-slate-500">(<?php echo e($version->uploader?->name); ?>)</span>
                        <?php if($version->notes): ?><p class="text-slate-600"><?php echo e($version->notes); ?></p><?php endif; ?>
                    </div>
                    <?php if($version->isPreviewable()): ?>
                        <button
                            type="button"
                            class="erp-btn-ghost text-xs"
                            data-preview-url="<?php echo e($version->previewUrl()); ?>"
                            data-preview-title="<?php echo e($version->original_name); ?>"
                            data-preview-pdf="<?php echo e($version->mime_type === 'application/pdf' ? '1' : '0'); ?>"
                            @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                        ><?php echo e(__('View')); ?></button>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No versions uploaded yet.')); ?></p>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', [App\Models\Artwork\ArtworkVersion::class, $request])): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.versions.store', $request)); ?>" enctype="multipart/form-data" data-turbo-frame="erp-main" class="mt-4 space-y-2">
                    <?php echo csrf_field(); ?>
                    <label class="block text-xs font-semibold text-slate-700 mb-1"><?php echo e(__('Artwork file')); ?></label>
                    <input type="file" name="file" class="erp-input w-full" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                    <label class="block text-xs font-semibold text-slate-700 mb-1"><?php echo e(__('Version notes')); ?></label>
                    <input type="text" name="notes" class="erp-input w-full" placeholder="<?php echo e(__('Optional notes for this version')); ?>">
                    <button class="erp-btn-secondary"><?php echo e(__('Upload version')); ?></button>
                </form>
            <?php else: ?>
                <?php if($request->lacksUploadedVersion()): ?>
                    <p class="mt-3 text-sm text-slate-500"><?php echo e(__('You do not have permission to upload artwork. Ask a designer or administrator to attach a file.')); ?></p>
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
            <h3 class="font-medium mb-3"><?php echo e(__('Reference files')); ?></h3>
            <?php $__empty_1 = true; $__currentLoopData = $request->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="text-sm py-1"><?php echo e($file->original_name); ?> (<?php echo e($file->file_type->value); ?>)</div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No reference files.')); ?></p>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.files.store', $request)); ?>" enctype="multipart/form-data" data-turbo-frame="erp-main" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" class="erp-input w-full" required>
                    <button class="erp-btn-secondary mt-2"><?php echo e(__('Upload reference')); ?></button>
                </form>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Comments & approvals')); ?></h3>
            <?php $__currentLoopData = $request->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm border-b py-2">
                    <span class="erp-badge"><?php echo e($comment->comment_type->value); ?></span>
                    <?php echo e($comment->user?->name); ?>: <?php echo e($comment->comment); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $request)): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.comments.store', $request)); ?>" class="mt-4 space-y-2">
                    <?php echo csrf_field(); ?>
                    <select name="comment_type" class="erp-input w-full">
                        <option value="internal"><?php echo e(__('Internal')); ?></option>
                        <option value="customer"><?php echo e(__('Customer')); ?></option>
                    </select>
                    <textarea name="comment" class="erp-input w-full" rows="2" required></textarea>
                    <button class="erp-btn-secondary"><?php echo e(__('Add comment')); ?></button>
                </form>
            <?php endif; ?>
            <?php $__currentLoopData = $request->approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm mt-3 text-slate-600">
                    <?php echo e($approval->decision->value); ?> — <?php echo e($approval->approver?->name); ?>

                    <?php if($approval->comments): ?> (<?php echo e($approval->comments); ?>) <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\requests\show.blade.php ENDPATH**/ ?>