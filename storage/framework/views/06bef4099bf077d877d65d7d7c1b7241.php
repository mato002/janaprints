<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $inline = (bool) ($inline ?? false);
    $compact = (bool) ($compact ?? false);
    $actions = [];
    $seenUrls = [];
    $seenKeys = [];

    $pushAction = function (array $action) use (&$actions, &$seenUrls, &$seenKeys): void {
        $url = (string) ($action['url'] ?? '');
        $label = (string) ($action['label'] ?? '');
        if ($url === '' || $label === '') {
            return;
        }

        $normalizedLabel = strtolower($label);
        $dedupeKey = match (true) {
            str_contains($normalizedLabel, 'job 360') => 'job360',
            str_contains($normalizedLabel, 'customer') => 'customer',
            str_contains($normalizedLabel, 'print') => 'print',
            str_contains($normalizedLabel, 'sales order') => 'sales_order',
            str_contains($normalizedLabel, 'artwork') => 'artwork',
            default => $normalizedLabel.'|'.$url,
        };

        if (isset($seenUrls[$url]) || isset($seenKeys[$dedupeKey])) {
            return;
        }

        $seenUrls[$url] = true;
        $seenKeys[$dedupeKey] = true;
        $actions[] = $action;
    };

    if (! empty($row['job_360_url'])) {
        $pushAction([
            'label' => __('View'),
            'url' => $row['job_360_url'],
            'type' => 'link',
            'slot' => 'view',
        ]);
    }

    foreach ($row['quick_actions'] ?? [] as $action) {
        if (! is_array($action)) {
            continue;
        }

        $label = strtolower((string) ($action['label'] ?? ''));
        if (str_contains($label, 'job 360') || str_contains($label, 'open job')) {
            continue;
        }

        $pushAction($action);
    }

    if (! empty($row['customer_360_url'])) {
        $pushAction([
            'label' => __('Customer 360'),
            'url' => $row['customer_360_url'],
            'type' => 'link',
        ]);
    }

    if (! empty($row['print_url'])) {
        $pushAction([
            'label' => $row['print_label'] ?? __('Print'),
            'url' => $row['print_url'],
            'type' => 'link',
            'target' => '_blank',
        ]);
    }

    $inlineActions = [];
    $moreActions = [];

    foreach ($actions as $action) {
        $label = strtolower((string) ($action['label'] ?? ''));
        $isPrimary = ($action['variant'] ?? '') === 'primary'
            || str_contains($label, 'start')
            || str_contains($label, 'assign')
            || str_contains($label, 'complete');

        if (($action['slot'] ?? null) === 'view') {
            $inlineActions[] = $action;
        } elseif ($isPrimary && count($inlineActions) < 3) {
            $inlineActions[] = $action;
        } else {
            $moreActions[] = $action;
        }
    }

    if ($inline && count($inlineActions) === 0 && count($actions) > 0) {
        $inlineActions[] = $actions[0];
        $moreActions = array_slice($actions, 1);
    }

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::turboFormAttributes();
?>

<?php if(count($inlineActions) > 0 || count($moreActions) > 0): ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['production-queue-row-actions', 'production-queue-row-actions--compact' => $compact || $inline]); ?>">
        <?php $__currentLoopData = $inlineActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $btnClass = 'production-queue-action-btn';
                if ($inline || $compact) {
                    $btnClass .= ' production-queue-action-btn--compact';
                }
                if (($action['variant'] ?? '') === 'primary') {
                    $btnClass .= ' production-queue-action-btn--primary';
                }
            ?>
            <?php if(($action['type'] ?? 'link') === 'post'): ?>
                <form method="POST" action="<?php echo e($action['url']); ?>" class="inline" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="<?php echo e($btnClass); ?>"><?php echo e($action['label']); ?></button>
                </form>
            <?php else: ?>
                <a
                    href="<?php echo e($action['url']); ?>"
                    class="<?php echo e($btnClass); ?>"
                    <?php if(($action['target'] ?? null) === '_blank'): ?>
                        target="_blank"
                        rel="noopener"
                    <?php else: ?>
                        <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                ><?php echo e($action['label']); ?></a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(count($moreActions) > 0): ?>
            <details class="production-queue-row-actions__more">
                <summary class="production-queue-action-btn production-queue-action-btn--compact"><?php echo e(__('More')); ?> ▾</summary>
                <div class="production-queue-row-actions__menu">
                    <?php $__currentLoopData = $moreActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(($action['type'] ?? 'link') === 'post'): ?>
                            <form method="POST" action="<?php echo e($action['url']); ?>" class="block" <?php $__currentLoopData = $formTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="production-queue-row-actions__menu-item"><?php echo e($action['label']); ?></button>
                            </form>
                        <?php else: ?>
                            <a
                                href="<?php echo e($action['url']); ?>"
                                class="production-queue-row-actions__menu-item"
                                <?php if(($action['target'] ?? null) === '_blank'): ?>
                                    target="_blank"
                                    rel="noopener"
                                <?php else: ?>
                                    <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            ><?php echo e($action['label']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </details>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/row-actions.blade.php ENDPATH**/ ?>