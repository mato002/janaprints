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
    <div class="overflow-x-auto mb-4">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Period')); ?></th>
                    <th><?php echo e(__('Forecast')); ?></th>
                    <th><?php echo e(__('Range')); ?></th>
                    <th><?php echo e(__('Confidence')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = ['next_month' => __('Next month'), 'next_quarter' => __('Next quarter'), 'next_year' => __('Next year')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $row = $data[$key] ?? null; ?>
                    <?php if($row): ?>
                        <tr>
                            <td><?php echo e($label); ?></td>
                            <td><?php echo e(number_format((float) ($row['forecast_value'] ?? 0), 2)); ?></td>
                            <td><?php echo e(number_format((float) ($row['lower_bound'] ?? 0), 2)); ?> – <?php echo e(number_format((float) ($row['upper_bound'] ?? 0), 2)); ?></td>
                            <td><?php echo e(($row['confidence_score'] ?? null) !== null ? number_format((float) $row['confidence_score'], 1).'%' : '—'); ?></td>
                        </tr>
                    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\executive-forecast-periods.blade.php ENDPATH**/ ?>