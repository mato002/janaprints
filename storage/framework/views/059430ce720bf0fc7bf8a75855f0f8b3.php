<?php
    $groups = $workspace['action_groups'] ?? [['key' => 'all', 'items' => $workspace['action_bar'] ?? []]];
?>

<?php if (isset($component)) { $__componentOriginal8048d2caaf5f0c5aca98de2f410e9e63 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8048d2caaf5f0c5aca98de2f410e9e63 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.action-bar','data' => ['groups' => $groups]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.action-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['groups' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groups)]); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quoteRequest)): ?>
        <div class="rw-actions__group" data-group="danger">
            <form method="POST" action="<?php echo e(route('admin.public-quote-requests.update-status', $quoteRequest)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <input type="hidden" name="status" value="spam">
                <button
                    type="submit"
                    class="rw-actions__btn rw-actions__btn--danger"
                    onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('Reject this quote request?'))->toHtml() ?>)"
                ><?php echo e(__('Reject')); ?></button>
            </form>
        </div>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8048d2caaf5f0c5aca98de2f410e9e63)): ?>
<?php $attributes = $__attributesOriginal8048d2caaf5f0c5aca98de2f410e9e63; ?>
<?php unset($__attributesOriginal8048d2caaf5f0c5aca98de2f410e9e63); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8048d2caaf5f0c5aca98de2f410e9e63)): ?>
<?php $component = $__componentOriginal8048d2caaf5f0c5aca98de2f410e9e63; ?>
<?php unset($__componentOriginal8048d2caaf5f0c5aca98de2f410e9e63); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\action-bar.blade.php ENDPATH**/ ?>