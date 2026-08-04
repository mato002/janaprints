<?php
    $header = $workspace['header'];
?>

<header class="qr-360__header">
    <div class="qr-360__header-top">
        <a href="<?php echo e(route('admin.public-quote-requests.index')); ?>" class="qr-360__back" data-turbo-frame="erp-main">
            ← <?php echo e(__('Quote Requests')); ?>

        </a>
        <div class="qr-360__header-pills">
            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $header['status_variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['status_variant'])]); ?><?php echo e($header['status_label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
            <span class="qr-360__pill qr-360__pill--neutral"><?php echo e($header['priority_label']); ?></span>
            <span class="qr-360__score qr-360__score--<?php echo e($workspace['lead_score']['variant']); ?>"><?php echo e($workspace['lead_score']['label']); ?></span>
        </div>
    </div>

    <div class="qr-360__header-body">
        <div class="qr-360__header-id">
            <h1 class="qr-360__ref"><?php echo e($header['reference']); ?></h1>
            <p class="qr-360__header-line">
                <span><?php echo e($header['customer_name']); ?></span>
                <span class="qr-360__sep">·</span>
                <span><?php echo e($header['service']); ?></span>
                <span class="qr-360__sep">·</span>
                <span><?php echo e($header['quantity']); ?> <?php echo e(__('units')); ?></span>
                <span class="qr-360__sep">·</span>
                <span><?php echo e($header['submitted_at']); ?></span>
            </p>
        </div>
        <dl class="qr-360__header-meta">
            <div>
                <dt><?php echo e(__('Assigned')); ?></dt>
                <dd><?php echo e($header['assigned_to']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Expected Value')); ?></dt>
                <dd><?php echo e($header['expected_value']); ?></dd>
            </div>
        </dl>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\header.blade.php ENDPATH**/ ?>