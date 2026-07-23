<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => $artwork->request_number,'heading' => $artwork->title,'subtitle' => $artwork->request_number]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artwork->request_number),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artwork->title),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artwork->request_number)]); ?>
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong><?php echo e(__('Status')); ?>:</strong> <?php echo $__env->make('client.partials.status-badge', ['status' => $artwork->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></p>
            <?php if($artwork->description): ?>
                <p><strong><?php echo e(__('Notes')); ?>:</strong> <?php echo e($artwork->description); ?></p>
            <?php endif; ?>
        </div>

        <?php if($previewUrl): ?>
            <div class="client-artwork-preview">
                <?php if($previewIsImage): ?>
                    <img src="<?php echo e($previewUrl); ?>" alt="<?php echo e($artwork->title); ?>" class="client-artwork-preview__image">
                <?php else: ?>
                    <iframe src="<?php echo e($previewUrl); ?>" title="<?php echo e($artwork->title); ?>" class="client-artwork-preview__frame"></iframe>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($canReview): ?>
            <div class="client-review-box">
                <h3 class="client-panel__title"><?php echo e(__('Review artwork')); ?></h3>
                <form method="POST" action="<?php echo e(route('client.artwork.review', $artwork)); ?>" class="client-review-form">
                    <?php echo csrf_field(); ?>
                    <fieldset class="client-radio-group">
                        <label><input type="radio" name="decision" value="approved" required> <?php echo e(__('Approve')); ?></label>
                        <label><input type="radio" name="decision" value="revision_requested"> <?php echo e(__('Request revisions')); ?></label>
                        <label><input type="radio" name="decision" value="rejected"> <?php echo e(__('Reject')); ?></label>
                    </fieldset>
                    <label for="comments" class="client-label"><?php echo e(__('Comments')); ?></label>
                    <textarea id="comments" name="comments" rows="4" class="client-input"><?php echo e(old('comments')); ?></textarea>
                    <button type="submit" class="client-btn"><?php echo e(__('Submit feedback')); ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\artwork\show.blade.php ENDPATH**/ ?>