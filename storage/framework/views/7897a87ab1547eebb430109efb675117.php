<?php
    $rail = $workspace['sidebar'];
    $next = $workspace['next_action'];
?>

<aside class="qr-360__rail">
    <section class="qr-360__card qr-360__card--rail">
        <div class="qr-360__rail-head">
            <h2 class="qr-360__card-title"><?php echo e(__('Opportunity')); ?></h2>
            <span class="qr-360__score qr-360__score--<?php echo e($workspace['lead_score']['variant']); ?>"><?php echo e($workspace['lead_score']['label']); ?></span>
        </div>

        <dl class="qr-360__rail-list">
            <div>
                <dt><?php echo e(__('Status')); ?></dt>
                <dd><?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $rail['status']->badgeVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rail['status']->badgeVariant())]); ?><?php echo e($rail['status']->workspaceLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Priority')); ?></dt>
                <dd><?php echo e($rail['priority']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Assigned User')); ?></dt>
                <dd><?php echo e($rail['assigned_to']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Expected Value')); ?></dt>
                <dd><?php echo e($rail['expected_value']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Probability')); ?></dt>
                <dd><?php echo e($rail['probability']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Follow-Up Date')); ?></dt>
                <dd><?php echo e($rail['follow_up_at']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Next Action')); ?></dt>
                <dd class="qr-360__rail-highlight"><?php echo e($next['label']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Last Activity')); ?></dt>
                <dd><?php echo e($rail['last_activity']?->diffForHumans() ?? '—'); ?></dd>
            </div>
        </dl>
    </section>

    <section class="qr-360__card qr-360__card--rail">
        <h2 class="qr-360__card-title"><?php echo e(__('Customer Contact')); ?></h2>
        <dl class="qr-360__rail-list">
            <div>
                <dt><?php echo e(__('Phone')); ?></dt>
                <dd><a href="tel:<?php echo e(preg_replace('/\s+/', '', $rail['phone'])); ?>" class="qr-360__field-link"><?php echo e($rail['phone']); ?></a></dd>
            </div>
            <div>
                <dt><?php echo e(__('Email')); ?></dt>
                <dd><a href="mailto:<?php echo e($rail['email']); ?>" class="qr-360__field-link"><?php echo e($rail['email']); ?></a></dd>
            </div>
            <div>
                <dt><?php echo e(__('Artwork Files')); ?></dt>
                <dd><?php echo e($rail['artwork_count']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Submitted')); ?></dt>
                <dd><?php echo e($rail['submitted_at']->format('d M Y')); ?></dd>
            </div>
        </dl>
    </section>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/customer-service/quote-requests/workspace/sidebar.blade.php ENDPATH**/ ?>