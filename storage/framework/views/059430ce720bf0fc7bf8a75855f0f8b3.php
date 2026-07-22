<section class="qr-360__action-bar" aria-label="<?php echo e(__('Commercial actions')); ?>">
    <div class="qr-360__action-bar-inner">
        <?php $__currentLoopData = $workspace['action_bar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(! empty($action['onclick'])): ?>
                <button type="button" class="qr-360__action-btn qr-360__action-btn--<?php echo e($action['variant'] ?? 'ghost'); ?>" onclick="<?php echo e($action['onclick']); ?>">
                    <?php echo e($action['label']); ?>

                </button>
            <?php else: ?>
                <a
                    href="<?php echo e($action['url']); ?>"
                    class="qr-360__action-btn qr-360__action-btn--<?php echo e($action['variant'] ?? 'outline'); ?>"
                    <?php if(! empty($action['external'])): ?> target="_blank" rel="noopener" <?php elseif(! str_starts_with($action['url'], '#')): ?> data-turbo-frame="erp-main" <?php endif; ?>
                ><?php echo e($action['label']); ?></a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quoteRequest)): ?>
            <form method="POST" action="<?php echo e(route('admin.public-quote-requests.update-status', $quoteRequest)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <input type="hidden" name="status" value="spam">
                <button type="submit" class="qr-360__action-btn qr-360__action-btn--danger" onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('Reject this quote request?'))->toHtml() ?>)">
                    <?php echo e(__('Reject Request')); ?>

                </button>
            </form>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\action-bar.blade.php ENDPATH**/ ?>