<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $executionState = $executionState ?? [];
    $primaryAction = $primaryAction ?? null;
    $linkActions = $linkActions ?? [];
    $needsOperator = (bool) ($executionState['needs_operator'] ?? false);
    $needsMachine = (bool) ($executionState['needs_machine'] ?? false);
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();

    $printAction = collect($linkActions)->first(fn ($link) => ($link['target'] ?? null) === '_blank');

    $actions = [];

    if ($needsOperator) {
        $actions[] = ['label' => __('Assign operator'), 'type' => 'anchor', 'url' => '#assign-operator', 'variant' => 'primary'];
    }
    if ($needsMachine) {
        $actions[] = ['label' => __('Assign machine'), 'type' => 'anchor', 'url' => '#assign-machine', 'variant' => 'primary'];
    }
    if ($primaryAction && ! $needsOperator && ! $needsMachine) {
        $actions[] = $primaryAction;
    }
    if ($printAction) {
        $actions[] = [
            'label' => $printAction['label'],
            'type' => 'link',
            'url' => $printAction['url'],
            'target' => '_blank',
            'variant' => 'secondary',
        ];
    }
?>

<?php if(! empty($actions)): ?>
    <div class="job-360-fab" aria-label="<?php echo e(__('Quick actions')); ?>">
        <div class="job-360-fab__inner">
            <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($action['type'] ?? '') === 'anchor'): ?>
                    <a href="<?php echo e($action['url']); ?>" class="job-360-fab__btn job-360-fab__btn--<?php echo e($action['variant'] ?? 'primary'); ?>">
                        <?php echo e($action['label']); ?>

                    </a>
                <?php elseif(($action['type'] ?? '') === 'post'): ?>
                    <form method="POST" action="<?php echo e($action['url']); ?>" class="job-360-fab__form" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="job-360-fab__btn job-360-fab__btn--<?php echo e($action['variant'] ?? 'primary'); ?>">
                            <?php echo e($action['label']); ?>

                        </button>
                    </form>
                <?php elseif(($action['type'] ?? '') === 'link'): ?>
                    <a
                        href="<?php echo e($action['url']); ?>"
                        class="job-360-fab__btn job-360-fab__btn--<?php echo e($action['variant'] ?? 'secondary'); ?>"
                        <?php if(($action['target'] ?? null) === '_blank'): ?> target="_blank" rel="noopener" <?php else: ?> <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
                    ><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\floating-action-bar.blade.php ENDPATH**/ ?>