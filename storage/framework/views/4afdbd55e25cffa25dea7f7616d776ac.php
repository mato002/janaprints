<section class="c360-kpi-strip" aria-label="<?php echo e(__('Customer KPIs')); ?>">
    <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="c360-kpi-card">
            <div class="c360-kpi-card__head">
                <?php if(! empty($kpi['icon'])): ?>
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $kpi['icon'],'class' => 'h-4 w-4 text-erp-accent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['icon']),'class' => 'h-4 w-4 text-erp-accent']); ?>
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
                <h3 class="c360-kpi-card__title"><?php echo e($kpi['label']); ?></h3>
            </div>
            <?php if(! empty($kpi['metrics'])): ?>
                <dl class="c360-kpi-card__metrics">
                    <?php $__currentLoopData = $kpi['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="c360-kpi-card__metric">
                            <dt><?php echo e($metric['label']); ?></dt>
                            <dd class="tabular-nums"><?php echo e($metric['value']); ?></dd>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </dl>
            <?php endif; ?>
            <?php if(! empty($kpi['warning'])): ?>
                <p class="mt-2 text-xs font-medium text-amber-700"><?php echo e($kpi['warning']); ?></p>
            <?php endif; ?>
            <?php if(! empty($kpi['placeholder'])): ?>
                <p class="c360-kpi-card__placeholder <?php echo e(! empty($kpi['metrics']) ? 'mt-2 border-t border-erp-border pt-2' : ''); ?>"><?php echo e($kpi['placeholder']); ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/workspace/kpi-strip.blade.php ENDPATH**/ ?>