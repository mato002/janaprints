<dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
    <div><dt class="text-xs text-slate-500"><?php echo e(__('Forecast')); ?></dt><dd class="font-medium"><?php echo e(number_format((float) ($forecast['forecast_value'] ?? 0), 2)); ?><?php echo e($suffix ?? ''); ?></dd></div>
    <div><dt class="text-xs text-slate-500"><?php echo e(__('Lower bound')); ?></dt><dd class="font-medium"><?php echo e(number_format((float) ($forecast['lower_bound'] ?? 0), 2)); ?><?php echo e($suffix ?? ''); ?></dd></div>
    <div><dt class="text-xs text-slate-500"><?php echo e(__('Upper bound')); ?></dt><dd class="font-medium"><?php echo e(number_format((float) ($forecast['upper_bound'] ?? 0), 2)); ?><?php echo e($suffix ?? ''); ?></dd></div>
    <div><dt class="text-xs text-slate-500"><?php echo e(__('Confidence')); ?></dt><dd class="font-medium"><?php echo e(($forecast['confidence_score'] ?? null) !== null ? number_format((float) $forecast['confidence_score'], 1).'%' : '—'); ?></dd></div>
</dl>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\executive-forecast-single.blade.php ENDPATH**/ ?>