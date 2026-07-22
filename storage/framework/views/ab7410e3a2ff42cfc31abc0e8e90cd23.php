<?php
    $queryExceptMonth = array_merge(request()->except('page', 'month'), ['view' => 'calendar']);
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mb-4 flex items-center justify-between gap-3">
        <a
            href="<?php echo e(route('admin.production.scheduling.index', array_merge($queryExceptMonth, ['month' => $calendar['prev_month']]))); ?>"
            class="erp-btn-secondary text-sm"
        >
            <?php echo e(__('Previous')); ?>

        </a>
        <h3 class="text-sm font-semibold text-erp-primary"><?php echo e($calendar['label']); ?></h3>
        <a
            href="<?php echo e(route('admin.production.scheduling.index', array_merge($queryExceptMonth, ['month' => $calendar['next_month']]))); ?>"
            class="erp-btn-secondary text-sm"
        >
            <?php echo e(__('Next')); ?>

        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[42rem] border-collapse text-sm">
            <thead>
                <tr class="text-xs uppercase tracking-wide text-slate-500">
                    <?php $__currentLoopData = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="border border-erp-border bg-erp-page px-2 py-2 text-center font-medium"><?php echo e(__($weekday)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $calendar['weeks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="align-top border border-erp-border p-1 min-h-[5rem] w-[14.28%] <?php echo e($day['in_month'] ? 'bg-white' : 'bg-erp-page/60'); ?> <?php echo e($day['is_today'] ? 'ring-2 ring-inset ring-erp-accent/40' : ''); ?>">
                                <div class="mb-1 text-xs font-medium tabular-nums <?php echo e($day['in_month'] ? 'text-slate-700' : 'text-slate-400'); ?>">
                                    <?php echo e($day['label']); ?>

                                </div>
                                <ul class="space-y-0.5">
                                    <?php $__currentLoopData = $day['jobs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li>
                                            <?php if(Route::has('admin.production.job-cards.show')): ?>
                                                <a
                                                    href="<?php echo e($job['url'] ?? route('admin.production.job-cards.show', $job['public_id'])); ?>"
                                                    class="block truncate rounded px-1 py-0.5 text-[10px] leading-tight <?php echo e($job['span'] === 'start' ? 'bg-erp-accent/15 text-erp-primary font-medium' : ($job['span'] === 'end' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700')); ?>"
                                                    title="<?php echo e($job['job_number']); ?> — <?php echo e($job['customer']); ?>"
                                                >
                                                    <?php echo e($job['job_number']); ?>

                                                </a>
                                            <?php else: ?>
                                                <span
                                                    class="block truncate rounded px-1 py-0.5 text-[10px] leading-tight bg-slate-100 text-slate-700"
                                                    title="<?php echo e($job['job_number']); ?> — <?php echo e($job['customer']); ?>"
                                                >
                                                    <?php echo e($job['job_number']); ?>

                                                </span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\scheduling\partials\calendar.blade.php ENDPATH**/ ?>