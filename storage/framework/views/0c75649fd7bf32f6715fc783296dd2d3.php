<?php
    $snap = $workspace['snapshot'];
?>

<?php if (isset($component)) { $__componentOriginalb327e04d2aba66fca2df8a26a48e286d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.section','data' => ['title' => __('Request details'),'tone' => 'muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Request details')),'tone' => 'muted']); ?>
    <div class="rw-hero-snapshot">
        <div class="rw-hero-snapshot__grid">
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Phone')); ?></span>
                <a href="tel:<?php echo e(preg_replace('/\s+/', '', $snap['phone'])); ?>" class="rw-hero-snapshot__link"><?php echo e($snap['phone']); ?></a>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Email')); ?></span>
                <a href="mailto:<?php echo e($snap['email']); ?>" class="rw-hero-snapshot__link"><?php echo e($snap['email']); ?></a>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Service')); ?></span>
                <span class="rw-hero-snapshot__field-value"><?php echo e($snap['service']); ?></span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Quantity')); ?></span>
                <span class="rw-hero-snapshot__field-value"><?php echo e($snap['quantity']); ?></span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Deadline')); ?></span>
                <span class="rw-hero-snapshot__field-value"><?php echo e($snap['deadline']); ?></span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label"><?php echo e(__('Source')); ?></span>
                <span class="rw-hero-snapshot__field-value"><?php echo e($snap['source']); ?></span>
            </div>
        </div>

        <?php if($snap['message']): ?>
            <div class="rw-hero-snapshot__note">
                <p class="rw-hero-snapshot__note-label"><?php echo e(__('Customer notes')); ?></p>
                <p class="whitespace-pre-wrap"><?php echo e($snap['message']); ?></p>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $attributes = $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $component = $__componentOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\snapshot.blade.php ENDPATH**/ ?>