<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $links = [
        ['tab' => 'quality', 'label' => __('QC'), 'theme' => 'qc'],
        ['tab' => 'dispatch', 'label' => __('Disp'), 'theme' => 'dispatch'],
        ['tab' => 'timeline', 'label' => __('Time'), 'theme' => 'history'],
        ['tab' => 'operations', 'label' => __('Ops'), 'theme' => 'production'],
    ];
?>

<div class="mes-quick-nav" aria-label="<?php echo e(__('Quick navigation')); ?>">
    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $link['tab']])); ?>"
            class="mes-kpi mes-kpi--<?php echo e($link['theme']); ?> mes-kpi--link"
            <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        >
            <span class="mes-kpi__label"><?php echo e($link['label']); ?></span>
            <span class="mes-kpi__value mes-kpi__value--arrow">→</span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\mes-quick-nav.blade.php ENDPATH**/ ?>