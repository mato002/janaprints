<section class="qr-360__card">
    <h2 class="qr-360__card-title"><?php echo e(__('Conversion Status')); ?></h2>

    <ol class="qr-360__conversion" role="list">
        <?php $__currentLoopData = $workspace['conversion']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="qr-360__conversion-step <?php echo e($step['linked'] ? 'qr-360__conversion-step--linked' : 'qr-360__conversion-step--pending'); ?>">
                <span class="qr-360__conversion-marker" aria-hidden="true">
                    <?php if($step['linked']): ?>
                        ✓
                    <?php else: ?>
                        ✗
                    <?php endif; ?>
                </span>
                <div class="qr-360__conversion-body">
                    <p class="qr-360__conversion-label"><?php echo e($step['label']); ?></p>
                    <p class="qr-360__conversion-state">
                        <?php if($step['linked']): ?>
                            <?php echo e($step['reference'] ?? __('Linked')); ?>

                        <?php else: ?>
                            <?php echo e(__('Not converted')); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <?php if(! $step['linked'] && ! empty($step['url'])): ?>
                    <a href="<?php echo e($step['url']); ?>" class="qr-360__conversion-link" data-turbo-frame="erp-main"><?php echo e(__('Convert')); ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\conversion-tracker.blade.php ENDPATH**/ ?>