<div
    class="public-loader"
    data-page-loader
    role="status"
    aria-live="polite"
    aria-label="Loading Jana Prints website"
>
    <div class="public-loader__stage">
        <div class="public-loader__sheet" aria-hidden="true"></div>

        <div class="public-loader__droplets" aria-hidden="true">
            <span class="public-loader__drop public-loader__drop--c" title="Cyan"></span>
            <span class="public-loader__drop public-loader__drop--m" title="Magenta"></span>
            <span class="public-loader__drop public-loader__drop--y" title="Yellow"></span>
            <span class="public-loader__drop public-loader__drop--k" title="Black"></span>
        </div>

        <div class="public-loader__spread" aria-hidden="true"></div>

        <div class="public-loader__artwork" aria-hidden="true">
            <div class="public-loader__artwork-inner"></div>
        </div>

        <div class="public-loader__brand">
            <?php if (isset($component)) { $__componentOriginala8631124f8a79f981399d6e3c172e3b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8631124f8a79f981399d6e3c172e3b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.brand-logo','data' => ['full' => true,'size' => 'lg','class' => 'public-loader__logo mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full' => true,'size' => 'lg','class' => 'public-loader__logo mx-auto']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $attributes = $__attributesOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $component = $__componentOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__componentOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
        </div>
    </div>

    <p class="public-loader__text">Printing Your Experience&hellip;</p>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/page-loader.blade.php ENDPATH**/ ?>