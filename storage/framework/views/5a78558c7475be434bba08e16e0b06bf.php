<section id="location" class="public-location-map-section public-section bg-white" data-testid="homepage-location" data-reveal-section aria-label="Jana Prints location map">
    <div class="public-container">
        <?php if (isset($component)) { $__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.contact-map','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.contact-map'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08)): ?>
<?php $attributes = $__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08; ?>
<?php unset($__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08)): ?>
<?php $component = $__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08; ?>
<?php unset($__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08); ?>
<?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/contact-map-section.blade.php ENDPATH**/ ?>