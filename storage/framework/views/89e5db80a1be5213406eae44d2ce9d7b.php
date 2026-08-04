<?php
    $summary = $summary ?? null;
    $warnings = $warnings ?? ($summary['warnings'] ?? []);
    $statusMessage = $statusMessage ?? null;
?>

<?php if($statusMessage): ?>
    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['variant' => 'success','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','class' => 'mb-4']); ?><?php echo e($statusMessage); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if(! empty($warnings)): ?>
    <?php if (isset($component)) { $__componentOriginald888329b8246e32afd68d2decbd25cf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald888329b8246e32afd68d2decbd25cf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.alert','data' => ['variant' => 'warning','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'warning','class' => 'mb-4']); ?>
        <ul class="list-disc space-y-1 pl-4 text-sm">
            <?php $__currentLoopData = $warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($warning); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $attributes = $__attributesOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__attributesOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald888329b8246e32afd68d2decbd25cf1)): ?>
<?php $component = $__componentOriginald888329b8246e32afd68d2decbd25cf1; ?>
<?php unset($__componentOriginald888329b8246e32afd68d2decbd25cf1); ?>
<?php endif; ?>
<?php endif; ?>

<?php if($summary): ?>
    <dl class="qr-360__pi-grid">
        <div>
            <dt><?php echo e(__('Analysis status')); ?></dt>
            <dd><?php echo e($summary['analysis_status_label'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Colour status')); ?></dt>
            <dd><?php echo e($summary['colour_analysis_status_label'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('File type')); ?></dt>
            <dd><?php echo e(strtoupper((string) ($summary['file_extension'] ?? '—'))); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Page count')); ?></dt>
            <dd><?php echo e($summary['page_count'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Dimensions')); ?></dt>
            <dd><?php echo e($summary['dimensions'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Colour coverage')); ?></dt>
            <dd>
                <?php if(($summary['cmyk_coverage_percent'] ?? null) !== null): ?>
                    <?php echo e(number_format((float) $summary['cmyk_coverage_percent'], 2)); ?>%
                <?php else: ?>
                    —
                <?php endif; ?>
            </dd>
        </div>
        <div>
            <dt><?php echo e(__('Coverage class')); ?></dt>
            <dd><?php echo e($summary['coverage_class_label'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt><?php echo e(__('Estimated ink')); ?></dt>
            <dd>
                <?php if(($summary['estimated_ink_ml'] ?? null) !== null): ?>
                    <?php echo e(number_format((float) $summary['estimated_ink_ml'], 2)); ?> ml
                    <?php if(($summary['estimated_ink_cost'] ?? null) !== null): ?>
                        · <?php echo e(number_format((float) $summary['estimated_ink_cost'], 2)); ?>

                    <?php endif; ?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </dd>
        </div>
        <div>
            <dt><?php echo e(__('Recommended machine')); ?></dt>
            <dd><?php echo e($summary['recommended_machine'] ?? '—'); ?></dd>
        </div>
    </dl>

    <?php if(! empty($summary['warnings'])): ?>
        <div class="qr-360__pi-warnings mt-4">
            <p class="qr-360__pi-warnings-title"><?php echo e(__('Warnings')); ?></p>
            <ul class="list-disc space-y-1 pl-4 text-sm text-amber-800">
                <?php $__currentLoopData = $summary['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($warning); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p class="text-sm text-slate-500"><?php echo e(__('No analysis results are available yet.')); ?></p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\printing-intelligence-summary.blade.php ENDPATH**/ ?>