<?php
    $leaveBalanceDays = collect($leave['balances'] ?? [])->sum(fn ($b) => (float) ($b['available'] ?? 0));
    $payrollReady = (bool) ($overview['payroll_profile_complete'] ?? true);
    $gross = $overview['gross_salary'] ?? null;
    $docsCount = $documents['all']->total();
    $assetsCount = $assets['issued']->count();
    $trainingCount = $training['assignments']->total();
    $performanceScore = $performance['kpis']['composite_score'] ?? null;
    $presentMtd = $attendance['summary']['present'] ?? 0;

    $kpis = [
        [
            'key' => 'present',
            'label' => __('Present (MTD)'),
            'value' => $presentMtd,
            'hint' => __('This month'),
            'tone' => 'info',
            'tab' => 'attendance',
        ],
        [
            'key' => 'leave',
            'label' => __('Leave Balance'),
            'value' => $leaveBalanceDays > 0 ? number_format($leaveBalanceDays, 1) : '0',
            'hint' => __('Days available'),
            'tone' => 'sky',
            'tab' => 'leave',
        ],
        [
            'key' => 'salary',
            'label' => __('Salary'),
            'value' => $gross !== null ? number_format((float) $gross, 0) : null,
            'hint' => $gross !== null ? __('Gross') : __('No compensation'),
            'tone' => 'violet',
            'tab' => 'compensation',
        ],
        [
            'key' => 'payroll',
            'label' => __('Payroll Status'),
            'value' => $payrollReady ? __('Ready') : __('Incomplete'),
            'hint' => $payrollReady ? __('Profile complete') : __('Action needed'),
            'tone' => $payrollReady ? 'success' : 'warning',
            'tab' => 'overview',
        ],
        [
            'key' => 'assets',
            'label' => __('Assets Issued'),
            'value' => $assetsCount,
            'hint' => __('In custody'),
            'tone' => 'slate',
            'tab' => 'assets',
        ],
        [
            'key' => 'documents',
            'label' => __('Documents'),
            'value' => $docsCount,
            'hint' => __('On file'),
            'tone' => 'indigo',
            'tab' => 'documents',
        ],
        [
            'key' => 'training',
            'label' => __('Training'),
            'value' => $trainingCount,
            'hint' => __('Assignments'),
            'tone' => 'teal',
            'tab' => 'training',
        ],
        [
            'key' => 'performance',
            'label' => __('Performance'),
            'value' => $performanceScore !== null ? number_format((float) $performanceScore, 0).'%' : null,
            'hint' => $performanceScore !== null ? __('Composite') : __('No review data'),
            'tone' => 'amber',
            'tab' => 'performance',
        ],
    ];
?>

<section class="employee-360__kpi-strip" aria-label="<?php echo e(__('Employee KPIs')); ?>">
    <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button
            type="button"
            class="employee-360__kpi employee-360__kpi--<?php echo e($kpi['tone']); ?>"
            @click="setTab(<?php echo \Illuminate\Support\Js::from($kpi['tab'])->toHtml() ?>)"
        >
            <span class="employee-360__kpi-label"><?php echo e($kpi['label']); ?></span>
            <span class="employee-360__kpi-value <?php echo e($kpi['value'] === null ? 'employee-360__kpi-value--empty' : ''); ?>">
                <?php echo e($kpi['value'] ?? '—'); ?>

            </span>
            <?php if($kpi['hint']): ?>
                <span class="employee-360__kpi-hint"><?php echo e($kpi['hint']); ?></span>
            <?php endif; ?>
        </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\kpi-strip.blade.php ENDPATH**/ ?>