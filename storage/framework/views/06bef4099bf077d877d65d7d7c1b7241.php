<?php
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
            'label' => __('Open Job 360'),
            'url' => $row['job_360_url'],
            'type' => 'link',
        ]);
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
            'label' => $row['print_label'] ?? __('Print job sheet'),
            'url' => $row['print_url'],
            'type' => 'link',
            'target' => '_blank',
        ]);
    }

    foreach ($row['quick_actions'] ?? [] as $action) {
        $pushAction(is_array($action) ? $action : []);
    }
?>

<?php if(count($actions) > 0): ?>
    <details class="relative inline-block text-left">
        <summary class="erp-btn-secondary cursor-pointer list-none text-xs py-2 px-3 [&::-webkit-details-marker]:hidden">
            <?php echo e(__('Actions')); ?>

            <span aria-hidden="true" class="ml-1">▾</span>
        </summary>
        <div class="absolute right-0 z-20 mt-1 min-w-[11rem] rounded-md border border-erp-border bg-white py-1 shadow-lg">
            <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($action['type'] ?? 'link') === 'post'): ?>
                    <form method="POST" action="<?php echo e($action['url']); ?>" class="block">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"><?php echo e($action['label']); ?></button>
                    </form>
                <?php else: ?>
                    <a
                        href="<?php echo e($action['url']); ?>"
                        class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50"
                        <?php if(($action['target'] ?? null) === '_blank'): ?>
                            target="_blank"
                            rel="noopener"
                        <?php else: ?>
                            data-turbo-frame="erp-main"
                        <?php endif; ?>
                    ><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </details>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/row-actions.blade.php ENDPATH**/ ?>