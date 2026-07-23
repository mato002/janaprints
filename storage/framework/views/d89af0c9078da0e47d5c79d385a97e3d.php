<?php
    $kpis = $kpis ?? [];
    $criticalKeys = ['quality', 'dispatch_score', 'operation_completion'];
    $advancedKeys = ['operators', 'materials', 'wastage', 'session_waste', 'serial_loss'];

    $critical = collect($kpis)->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $criticalKeys, true))->values();
    $advanced = collect($kpis)->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $advancedKeys, true))->values();
?>

<?php if($critical->isNotEmpty() || $advanced->isNotEmpty()): ?>
    <section class="job-360-performance mb-4" aria-label="<?php echo e(__('Performance metrics')); ?>">
        <?php if($critical->isNotEmpty()): ?>
            <div class="job-360-performance__critical">
                <?php $__currentLoopData = $critical; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'job-360-kpi',
                        'job-360-kpi--warning' => ! empty($kpi['warning']),
                        'job-360-kpi--'.($kpi['key'] ?? 'default'),
                    ]); ?>">
                        <div class="job-360-kpi__head">
                            <?php if(! empty($kpi['icon'])): ?>
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $kpi['icon'],'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['icon']),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            <?php endif; ?>
                            <h3 class="job-360-kpi__title"><?php echo e($kpi['label']); ?></h3>
                        </div>
                        <?php if(! empty($kpi['metrics'])): ?>
                            <dl class="job-360-kpi__metrics">
                                <?php $__currentLoopData = $kpi['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div>
                                        <dt><?php echo e($metric['label']); ?></dt>
                                        <dd class="tabular-nums"><?php echo e($metric['value']); ?></dd>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </dl>
                        <?php endif; ?>
                        <?php if(! empty($kpi['warning'])): ?>
                            <p class="job-360-kpi__warning"><?php echo e($kpi['warning']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($advanced->isNotEmpty()): ?>
            <details class="job-360-performance__advanced">
                <summary>
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chart-pie','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chart-pie','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    <?php echo e(__('Performance details')); ?>

                    <span class="job-360-performance__count"><?php echo e($advanced->count()); ?></span>
                </summary>
                <div class="job-360-performance__advanced-grid">
                    <?php $__currentLoopData = $advanced; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="job-360-kpi job-360-kpi--compact">
                            <div class="job-360-kpi__head">
                                <?php if(! empty($kpi['icon'])): ?>
                                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $kpi['icon'],'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['icon']),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                                <?php endif; ?>
                                <h3 class="job-360-kpi__title"><?php echo e($kpi['label']); ?></h3>
                            </div>
                            <?php if(! empty($kpi['metrics'])): ?>
                                <dl class="job-360-kpi__metrics">
                                    <?php $__currentLoopData = $kpi['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div>
                                            <dt><?php echo e($metric['label']); ?></dt>
                                            <dd class="tabular-nums"><?php echo e($metric['value']); ?></dd>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </dl>
                            <?php elseif(! empty($kpi['placeholder'])): ?>
                                <p class="job-360-kpi__placeholder"><?php echo e($kpi['placeholder']); ?></p>
                            <?php endif; ?>
                            <?php if(! empty($kpi['warning'])): ?>
                                <p class="job-360-kpi__warning"><?php echo e($kpi['warning']); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </details>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/performance-section.blade.php ENDPATH**/ ?>