<?php
    $fromDesk = ($fromDesk ?? false) || request('from') === 'designer-desk';
    $hasComments = $request->comments->isNotEmpty();
    $hasApprovals = $request->approvals->isNotEmpty();
?>

<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title"><?php echo e(__('Comments & approvals')); ?></h2>

    <?php if($hasComments): ?>
        <div class="artwork-detail-comments__timeline">
            <?php $__currentLoopData = $request->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="artwork-detail-comment">
                    <div class="artwork-detail-comment__avatar" aria-hidden="true">
                        <?php echo e(strtoupper(substr($comment->user?->name ?? '?', 0, 1))); ?>

                    </div>
                    <div class="artwork-detail-comment__body">
                        <div class="artwork-detail-comment__meta">
                            <span class="artwork-detail-comment__author"><?php echo e($comment->user?->name ?? __('Unknown')); ?></span>
                            <span class="erp-badge"><?php echo e(str_replace('_', ' ', ucfirst($comment->comment_type->value))); ?></span>
                            <?php if($comment->created_at): ?>
                                <span class="artwork-detail-comment__time"><?php echo e($comment->created_at->format('d M Y H:i')); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="artwork-detail-comment__text"><?php echo e($comment->comment); ?></p>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php $__currentLoopData = $request->approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="artwork-detail-approval">
            <strong><?php echo e(str_replace('_', ' ', ucfirst($approval->decision->value))); ?></strong>
            — <?php echo e($approval->approver?->name); ?>

            <?php if($approval->comments): ?>
                <span class="text-slate-500">(<?php echo e($approval->comments); ?>)</span>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $request)): ?>
        <form
            method="POST"
            action="<?php echo e(route('admin.artwork.comments.store', $request)); ?>"
            class="artwork-detail-comments__form <?php if(! $hasComments && ! $hasApprovals): ?> !border-t-0 !pt-0 <?php endif; ?>"
        >
            <?php echo csrf_field(); ?>
            <?php if($fromDesk): ?>
                <input type="hidden" name="from" value="designer-desk">
            <?php endif; ?>
            <div class="artwork-detail-comments__form-row">
                <div class="artwork-detail-comments__visibility">
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Visibility')); ?></label>
                    <select name="comment_type" class="erp-input w-full text-sm">
                        <option value="internal"><?php echo e(__('Internal')); ?></option>
                        <option value="customer"><?php echo e(__('Customer')); ?></option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Comment')); ?></label>
                <textarea name="comment" class="erp-input min-h-[6rem] w-full text-sm" rows="3" required placeholder="<?php echo e(__('Notes for the team…')); ?>"></textarea>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Add comment')); ?></button>
        </form>
    <?php endif; ?>

    <?php if(! $hasComments && ! $hasApprovals && ! auth()->user()?->can('view', $request)): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No comments or approvals yet.')); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/requests/partials/comments-panel.blade.php ENDPATH**/ ?>