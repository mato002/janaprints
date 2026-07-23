<?php
    $periods = $payslips
        ->map(fn ($p) => $p->payrollRun?->period_start?->format('Y-m'))
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();
?>

<section class="space-y-4">
    <?php if($periods->isNotEmpty()): ?>
        <form method="GET" action="<?php echo e(route('ess.dashboard')); ?>" class="ess-card flex flex-col gap-3 sm:flex-row sm:items-end">
            <input type="hidden" name="tab" value="payslips">
            <div class="flex-1">
                <label class="ess-label" for="period"><?php echo e(__('Filter by period')); ?></label>
                <select id="period" name="period" class="ess-input w-full">
                    <option value=""><?php echo e(__('All periods')); ?></option>
                    <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($period); ?>" <?php if(request('period') === $period): echo 'selected'; endif; ?>><?php echo e($period); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="ess-btn ess-btn--primary w-full sm:w-auto"><?php echo e(__('Apply filter')); ?></button>
        </form>
    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $payslips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payslip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="ess-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold"><?php echo e($payslip->reference ?? __('Payslip')); ?></p>
                    <p class="text-sm text-erp-muted">
                        <?php echo e($payslip->payrollRun?->period_start?->format('d M Y')); ?>

                        —
                        <?php echo e($payslip->payrollRun?->period_end?->format('d M Y')); ?>

                    </p>
                    <p class="mt-1 text-sm"><?php echo e(__('Net pay')); ?>: <strong>KES <?php echo e(number_format((float) $payslip->net_pay, 2)); ?></strong></p>
                </div>
                <a href="<?php echo e(route('ess.payslips.download', $payslip)); ?>" class="ess-btn ess-btn--primary w-full sm:w-auto"><?php echo e(__('Download PDF')); ?></a>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="ess-card text-sm text-erp-muted"><?php echo e(__('No released payslips available.')); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\payslips.blade.php ENDPATH**/ ?>