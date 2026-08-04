<?php
    use App\Support\Inventory\StoreDeskViews;
?>

<?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $registerTitle,'description' => $registerDescription ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registerTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registerDescription ?? null)]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <?php if($registerCanCreate ?? false): ?>
            <a
                href="<?php echo e($registerCreateUrl); ?>"
                class="erp-btn-primary"
                <?php if($registerCreateModal ?? false): ?> data-erp-modal-open <?php endif; ?>
            ><?php echo e($registerCreateLabel); ?></a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

<?php switch($activeStoreView):
    case (StoreDeskViews::BALANCES): ?>
        <?php echo $__env->make('admin.inventory.store.partials.balances-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::MOVEMENTS): ?>
        <?php echo $__env->make('admin.inventory.movements.partials.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::ALERTS): ?>
        <?php echo $__env->make('admin.inventory.alerts.partials.content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::RECEIPTS): ?>
        <?php echo $__env->make('admin.inventory.receipts.partials.table', ['fromStoreDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::ISSUES): ?>
        <?php echo $__env->make('admin.inventory.issues.partials.table', ['fromStoreDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::TRANSFERS): ?>
        <?php echo $__env->make('admin.inventory.transfers.partials.table', ['fromStoreDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
    <?php case (StoreDeskViews::ADJUSTMENTS): ?>
        <?php echo $__env->make('admin.inventory.adjustments.partials.table', ['fromStoreDesk' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php break; ?>
<?php endswitch; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\register-panel.blade.php ENDPATH**/ ?>