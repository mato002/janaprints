<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items']));

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

foreach (array_filter((['items']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<ul class="space-y-4" role="list">
    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $userName = is_array($log) ? ($log['user_name'] ?? null) : ($log->user?->name ?? null);
            $message = is_array($log) ? ($log['message'] ?? null) : null;
            $action = is_array($log) ? ($log['action'] ?? '') : ($log->action ?? '');
            $modelType = is_array($log) ? ($log['model_type'] ?? null) : ($log->model_type ?? null);
            $modelId = is_array($log) ? ($log['model_id'] ?? null) : ($log->model_id ?? null);
            $createdAt = is_array($log) ? ($log['created_at'] ?? null) : ($log->created_at ?? null);
            $ipAddress = is_array($log) ? ($log['ip_address'] ?? null) : ($log->ip_address ?? null);
        ?>
        <li class="relative flex gap-4 pl-6">
            <span class="absolute left-0 top-1.5 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full bg-erp-accent ring-4 ring-erp-accent/10"></span>
            </span>
            <?php if(! $loop->last): ?>
                <span class="absolute left-[5px] top-4 h-full w-px bg-erp-border" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="min-w-0 flex-1 pb-1">
                <p class="text-sm text-erp-primary">
                    <?php if($message): ?>
                        <?php echo e($message); ?>

                    <?php else: ?>
                        <span class="font-medium"><?php echo e($userName ?? __('System')); ?></span>
                        <span class="text-slate-500"><?php echo e($action); ?></span>
                        <?php if($modelType): ?>
                            <span class="text-slate-400"><?php echo e(class_basename($modelType)); ?> #<?php echo e($modelId); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
                <p class="mt-0.5 text-xs text-slate-400">
                    <?php if($createdAt): ?>
                        <?php
                            $timestamp = $createdAt instanceof \DateTimeInterface
                                ? $createdAt
                                : (is_string($createdAt) || is_numeric($createdAt) ? $createdAt : null);
                        ?>
                        <?php if($timestamp !== null): ?>
                            <?php echo e(\Illuminate\Support\Carbon::parse($timestamp)->diffForHumans()); ?>

                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($ipAddress): ?>
                        · <?php echo e($ipAddress); ?>

                    <?php endif; ?>
                </p>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="py-8 text-center text-sm text-slate-500"><?php echo e(__('No recent activity.')); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\activity-timeline.blade.php ENDPATH**/ ?>