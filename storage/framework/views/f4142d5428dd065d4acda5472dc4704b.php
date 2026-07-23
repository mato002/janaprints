<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['logs', 'compact' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['logs', 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $items = $logs instanceof \Illuminate\Support\Collection
        ? app(\App\Support\Communications\CommunicationLogService::class)->timelinePayload($logs)
        : $logs;
?>

<ul class="communication-timeline space-y-0" role="list">
    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="relative flex gap-4 <?php echo e($compact ? 'py-3' : 'py-4'); ?> pl-6">
            <span class="absolute left-0 top-4 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full bg-erp-accent ring-4 ring-erp-accent/10"></span>
            </span>
            <?php if(! $loop->last): ?>
                <span class="absolute left-[5px] top-5 h-full w-px bg-erp-border" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="<?php echo e($item['url']); ?>" data-turbo-frame="erp-main" class="text-sm font-semibold text-erp-primary hover:text-erp-accent">
                            <?php echo e($item['subject'] ?: Str::limit($item['message'], 48)); ?>

                        </a>
                        <p class="mt-0.5 text-xs text-slate-500">
                            <?php echo e($item['channel_label']); ?> · <?php echo e($item['type_label']); ?>

                            <?php if($item['recipient']): ?>
                                · <?php echo e($item['recipient']); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase <?php echo e($item['status_badge']); ?>">
                        <?php echo e($item['status_label']); ?>

                    </span>
                </div>
                <?php if (! ($compact)): ?>
                    <p class="mt-1 text-sm text-slate-600 line-clamp-2"><?php echo e($item['message']); ?></p>
                <?php endif; ?>
                <p class="mt-1 text-[10px] text-slate-400">
                    <?php echo e($item['reference_number']); ?>

                    · <?php echo e(\Illuminate\Support\Carbon::parse($item['created_at'])->diffForHumans()); ?>

                </p>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="py-6 text-center text-sm text-slate-500"><?php echo e(__('No communications recorded yet.')); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/communication-timeline.blade.php ENDPATH**/ ?>