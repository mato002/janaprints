<?php
    $assets = $dashboard['asset_snapshot'] ?? null;
    $links = $assets['links'] ?? [];
?>

<?php if(! empty($assets['visible'])): ?>
    <section class="exec-panel exec-panel--assets">
        <div class="exec-panel__head exec-panel__head--split">
            <h2 class="exec-panel__title"><?php echo e(__('Asset Snapshot')); ?></h2>
            <?php if($links !== []): ?>
                <nav class="exec-finance-links" aria-label="<?php echo e(__('Asset intelligence')); ?>">
                    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($link['url']); ?>" data-turbo-frame="erp-main" class="exec-finance-links__item"><?php echo e($link['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            <?php endif; ?>
        </div>
        <dl class="exec-dl exec-dl--grid">
            <div class="exec-dl__row"><dt><?php echo e(__('Asset Count')); ?></dt><dd><?php echo e($assets['asset_count'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Net Book Value')); ?></dt><dd><?php echo e($assets['net_book_value'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Depreciation MTD')); ?></dt><dd><?php echo e($assets['depreciation_mtd'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Warranty Expiry')); ?></dt><dd><?php echo e($assets['warranty_expiry'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Assets Requiring Service')); ?></dt><dd><?php echo e($assets['requiring_service'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Critical Assets')); ?></dt><dd><?php echo e($assets['critical_assets'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('End-of-Life Assets')); ?></dt><dd><?php echo e($assets['end_of_life'] ?? '—'); ?></dd></div>
        </dl>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\asset-snapshot.blade.php ENDPATH**/ ?>