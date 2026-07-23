<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>
<div class="production-floor-board mb-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
    <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e(WorkspaceEmbed::url(route('admin.production.floor', $card['filter'] ?? []))); ?>"
            class="block transition-opacity hover:opacity-90"
            data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
            data-turbo-action="advance"
        >
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => strtoupper($card['label']),'value' => $card['value'],'hint' => $card['hint'] ?? null,'class' => 'production-floor-board__card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(strtoupper($card['label'])),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['hint'] ?? null),'class' => 'production-floor-board__card']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/partials/summary-strip.blade.php ENDPATH**/ ?>