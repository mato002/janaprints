<?php
    use App\Enums\ArtworkRequestStatus;

    $needsVersion = $request->lacksUploadedVersion();
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $canUpload = auth()->user()?->can('create', [\App\Models\Artwork\ArtworkVersion::class, $request]) ?? false;

    $statusHeadline = match ($request->status) {
        ArtworkRequestStatus::Requested => __('Awaiting design'),
        ArtworkRequestStatus::InDesign => __('Design in progress'),
        ArtworkRequestStatus::Submitted => __('Awaiting approval'),
        ArtworkRequestStatus::Approved => __('Approved'),
        ArtworkRequestStatus::RevisionRequested => __('Revision requested'),
        ArtworkRequestStatus::Rejected => __('Rejected'),
    };

    $guidance = match (true) {
        $request->status === ArtworkRequestStatus::Requested => __('Assign a designer, start design, or upload a version before submitting for approval.'),
        $request->status === ArtworkRequestStatus::InDesign && $needsVersion => __('Upload at least one artwork version before submitting for approval.'),
        $needsVersion => __('No artwork file is attached yet. Upload a version below to unblock this request.'),
        default => null,
    };
?>

<div class="artwork-detail-card artwork-detail-card--workflow">
    <h2 class="artwork-detail-card__title"><?php echo e(__('Workflow')); ?></h2>
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
    <p class="artwork-detail-workflow__status"><?php echo e($statusHeadline); ?></p>
    <?php if($guidance): ?>
        <p class="artwork-detail-workflow__hint"><?php echo e($guidance); ?></p>
    <?php endif; ?>

    <div class="mt-4 flex flex-wrap gap-2">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign', $request)): ?>
            <form method="POST" action="<?php echo e(route('admin.artwork.assign', $request)); ?>" class="flex flex-wrap items-end gap-2">
                <?php echo csrf_field(); ?>
                <?php if($fromDesk): ?>
                    <input type="hidden" name="from" value="designer-desk">
                <?php endif; ?>
                <select name="assigned_designer_id" class="erp-input text-sm" required>
                    <option value=""><?php echo e(__('Assign designer')); ?></option>
                    <?php $__currentLoopData = \App\Models\User::query()->where('company_id', $request->company_id)->where('is_active', true)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($designer->id); ?>" <?php if($request->assigned_designer_id === $designer->id): echo 'selected'; endif; ?>><?php echo e($designer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Assign')); ?></button>
            </form>
        <?php endif; ?>

        <?php if($canUpload && $request->status->isEditable()): ?>
            <a
                href="#artwork-versions-upload"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'erp-btn-primary text-sm' => $needsVersion,
                    'erp-btn-secondary text-sm' => ! $needsVersion,
                ]); ?>"
            ><?php echo e(__('Upload artwork')); ?></a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('startDesign', $request)): ?>
            <form method="POST" action="<?php echo e(route('admin.artwork.start-design', $request)); ?>">
                <?php echo csrf_field(); ?>
                <?php if($fromDesk): ?>
                    <input type="hidden" name="from" value="designer-desk">
                <?php endif; ?>
                <button type="submit" class="erp-btn-secondary text-sm">
                    <?php echo e($request->status === ArtworkRequestStatus::Requested ? __('Start design') : __('Resume design')); ?>

                </button>
            </form>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('submit', $request)): ?>
            <form method="POST" action="<?php echo e(route('admin.artwork.submit', $request)); ?>">
                <?php echo csrf_field(); ?>
                <?php if($fromDesk): ?>
                    <input type="hidden" name="from" value="designer-desk">
                <?php endif; ?>
                <button
                    type="submit"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'erp-btn-primary text-sm' => ! $needsVersion,
                        'erp-btn-secondary text-sm opacity-60' => $needsVersion,
                    ]); ?>"
                ><?php echo e(__('Submit for approval')); ?></button>
            </form>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $request)): ?>
            <?php if($request->status === ArtworkRequestStatus::Submitted && $needsVersion): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.approve', $request)); ?>" class="flex flex-wrap items-end gap-2">
                    <?php echo csrf_field(); ?>
                    <?php if($fromDesk): ?>
                        <input type="hidden" name="from" value="designer-desk">
                    <?php endif; ?>
                    <input type="hidden" name="decision" value="rejected">
                    <input type="text" name="comments" class="erp-input text-sm" placeholder="<?php echo e(__('Rejection reason')); ?>">
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Reject request')); ?></button>
                </form>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('startDesign', $request)): ?>
                    <form method="POST" action="<?php echo e(route('admin.artwork.start-design', $request)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php if($fromDesk): ?>
                            <input type="hidden" name="from" value="designer-desk">
                        <?php endif; ?>
                        <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Return to design')); ?></button>
                    </form>
                <?php endif; ?>
            <?php elseif($request->canApproveOrRequestRevision()): ?>
                <form method="POST" action="<?php echo e(route('admin.artwork.approve', $request)); ?>" class="flex flex-wrap items-end gap-2">
                    <?php echo csrf_field(); ?>
                    <?php if($fromDesk): ?>
                        <input type="hidden" name="from" value="designer-desk">
                    <?php endif; ?>
                    <select name="decision" class="erp-input text-sm" required>
                        <option value="approved"><?php echo e(__('Approve')); ?></option>
                        <option value="revision_requested"><?php echo e(__('Request revision')); ?></option>
                        <option value="rejected"><?php echo e(__('Reject')); ?></option>
                    </select>
                    <input type="text" name="comments" class="erp-input text-sm" placeholder="<?php echo e(__('Comments')); ?>">
                    <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Record decision')); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/partials/workflow-panel.blade.php ENDPATH**/ ?>