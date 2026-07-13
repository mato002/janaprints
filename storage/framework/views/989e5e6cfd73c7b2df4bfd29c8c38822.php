<?php
    $ops = $dashboard['today_ops'];
    $utilization = (int) ($ops['machine_utilization'] ?? 0);
    $utilVariant = $utilization >= 75 ? 'success' : ($utilization >= 40 ? 'default' : 'warning');
    $purchases = (int) ($ops['purchases_pending'] ?? 0);
    $purchasePct = min(100, $purchases * 20);
?>

<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__("Today's Operations")); ?></h2>
    </div>
    <div class="exec-ops-grid">
        <?php if (isset($component)) { $__componentOriginal017e38b992490db8036533050d5219e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal017e38b992490db8036533050d5219e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-progress-widget','data' => ['label' => __('Machine utilization'),'value' => $utilization.'%','percent' => $utilization,'variant' => $utilVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-progress-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Machine utilization')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($utilization.'%'),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($utilization),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($utilVariant)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $attributes = $__attributesOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__attributesOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $component = $__componentOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__componentOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal017e38b992490db8036533050d5219e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal017e38b992490db8036533050d5219e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-progress-widget','data' => ['label' => __('Deliveries today'),'value' => (string) ($ops['deliveries_today'] ?? 0),'percent' => min(100, ((int) ($ops['deliveries_today'] ?? 0)) * 15)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-progress-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Deliveries today')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($ops['deliveries_today'] ?? 0)),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(min(100, ((int) ($ops['deliveries_today'] ?? 0)) * 15))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $attributes = $__attributesOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__attributesOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $component = $__componentOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__componentOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal017e38b992490db8036533050d5219e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal017e38b992490db8036533050d5219e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-progress-widget','data' => ['label' => __('Jobs scheduled today'),'value' => (string) ($ops['jobs_today'] ?? 0),'percent' => min(100, ((int) ($ops['jobs_today'] ?? 0)) * 12)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-progress-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Jobs scheduled today')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) ($ops['jobs_today'] ?? 0)),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(min(100, ((int) ($ops['jobs_today'] ?? 0)) * 12))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $attributes = $__attributesOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__attributesOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $component = $__componentOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__componentOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal017e38b992490db8036533050d5219e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal017e38b992490db8036533050d5219e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-progress-widget','data' => ['label' => __('Purchases awaiting approval'),'value' => (string) $purchases,'percent' => $purchasePct,'variant' => $purchases > 0 ? 'warning' : 'default']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-progress-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Purchases awaiting approval')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) $purchases),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($purchasePct),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($purchases > 0 ? 'warning' : 'default')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $attributes = $__attributesOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__attributesOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $component = $__componentOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__componentOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
        <div class="exec-ops-grid__wide">
            <?php if (isset($component)) { $__componentOriginal017e38b992490db8036533050d5219e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal017e38b992490db8036533050d5219e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-progress-widget','data' => ['label' => __('Collections expected'),'value' => $ops['collections_display'] ?? '—','percent' => ($ops['collections_display'] ?? '—') === '—' ? null : 50]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-progress-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Collections expected')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ops['collections_display'] ?? '—'),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($ops['collections_display'] ?? '—') === '—' ? null : 50)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $attributes = $__attributesOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__attributesOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal017e38b992490db8036533050d5219e5)): ?>
<?php $component = $__componentOriginal017e38b992490db8036533050d5219e5; ?>
<?php unset($__componentOriginal017e38b992490db8036533050d5219e5); ?>
<?php endif; ?>
            <?php if(($ops['collections_display'] ?? '—') === '—'): ?>
                <p class="mt-1 text-[10px] text-slate-500"><?php echo e(__('Collections tracking connects with finance.')); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/dashboard/partials/today-ops.blade.php ENDPATH**/ ?>