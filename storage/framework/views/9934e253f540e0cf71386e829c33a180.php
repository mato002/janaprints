<?php
    $salesOpportunities = collect($dashboard['sales_opportunities'] ?? []);
    $groups = [
        'critical' => ['label' => __('Critical'), 'class' => 'exec-attention-group--critical', 'severities' => ['danger']],
        'warning' => ['label' => __('Warning'), 'class' => 'exec-attention-group--warning', 'severities' => ['warning']],
        'normal' => ['label' => __('Normal'), 'class' => 'exec-attention-group--normal', 'severities' => ['muted']],
    ];
?>

<section class="exec-panel exec-panel--attention" aria-label="<?php echo e(__('Attention center')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Attention Center')); ?></h2>
        <span class="exec-attention-ribbon"><?php echo e(__('Action required')); ?></span>
    </div>

    <div class="exec-attention-groups">
        <?php if($salesOpportunities->isNotEmpty()): ?>
            <div class="exec-attention-group exec-attention-group--opportunity">
                <h3 class="exec-attention-group__title"><?php echo e(__('Sales Opportunities')); ?></h3>
                <ul class="exec-attention-list" role="list">
                    <?php $__currentLoopData = $salesOpportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $count = (string) ($item['count'] ?? 0);
                            $href = ! empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null;
                        ?>
                        <li>
                            <?php if($href): ?>
                                <a href="<?php echo e($href); ?>" data-turbo-frame="erp-main" class="exec-alert-row exec-alert-row--link exec-alert-row--opportunity">
                            <?php else: ?>
                                <div class="exec-alert-row exec-alert-row--opportunity">
                            <?php endif; ?>
                                <span class="exec-alert-row__label"><?php echo e($item['label']); ?></span>
                                <span class="exec-alert-badge exec-alert-badge--opportunity"><?php echo e($count); ?></span>
                                <?php if(! empty($item['hint'])): ?>
                                    <span class="exec-alert-row__hint"><?php echo e($item['hint']); ?></span>
                                <?php endif; ?>
                            <?php if($href): ?>
                                </a>
                            <?php else: ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $items = collect($dashboard['attention'])
                    ->filter(fn ($item) => in_array($item['severity'], $group['severities'], true));
            ?>
            <?php if($items->isNotEmpty()): ?>
                <div class="exec-attention-group <?php echo e($group['class']); ?>">
                    <h3 class="exec-attention-group__title"><?php echo e($group['label']); ?></h3>
                    <ul class="exec-attention-list" role="list">
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $count = $item['display'] ?? (string) ($item['count'] ?? 0);
                                $numericCount = is_numeric($item['count'] ?? null) ? (int) $item['count'] : null;
                                $href = ! empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null;
                                $isClear = $numericCount === 0 && ($item['display'] ?? null) !== '—';
                                $badgeClass = match (true) {
                                    $isClear && $groupKey === 'critical' => 'exec-alert-badge--clear',
                                    $item['severity'] === 'danger' => 'exec-alert-badge--critical',
                                    $item['severity'] === 'warning' => 'exec-alert-badge--warning',
                                    default => 'exec-alert-badge--normal',
                                };
                            ?>
                            <li>
                                <?php if($href): ?>
                                    <a href="<?php echo e($href); ?>" data-turbo-frame="erp-main" class="exec-alert-row exec-alert-row--link">
                                <?php else: ?>
                                    <div class="exec-alert-row">
                                <?php endif; ?>
                                    <span class="exec-alert-row__label"><?php echo e($item['label']); ?></span>
                                    <span class="exec-alert-badge <?php echo e($badgeClass); ?>">
                                        <?php if($isClear && $groupKey === 'critical'): ?>
                                            <?php echo e(__('Clear')); ?>

                                        <?php else: ?>
                                            <?php echo e($count); ?>

                                        <?php endif; ?>
                                    </span>
                                    <?php if(! empty($item['hint'])): ?>
                                        <span class="exec-alert-row__hint"><?php echo e($item['hint']); ?></span>
                                    <?php endif; ?>
                                <?php if($href): ?>
                                    </a>
                                <?php else: ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/dashboard/partials/attention-center.blade.php ENDPATH**/ ?>