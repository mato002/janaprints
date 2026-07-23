<?php if(! ($embeddedInDesk ?? false)): ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Commercial approvals queue'),'description' => __('Pending quotations, sales orders, and artwork submissions.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Commercial approvals queue')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Pending quotations, sales orders, and artwork submissions.'))]); ?>
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
<?php else: ?>
    <div class="mb-3">
        <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Approvals')); ?></h2>
        <p class="text-xs text-slate-600"><?php echo e(__('Pending quotations, sales orders, and artwork submissions.')); ?></p>
    </div>
<?php endif; ?>

<?php echo $__env->make('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_quotations'], 'title' => __('Pending Quotations')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_sales_orders'], 'title' => __('Pending Sales Orders')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_artwork'], 'title' => __('Pending Artwork')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_approved'], 'title' => __('Recently Approved')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_rejected'], 'title' => __('Recently Rejected')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/approvals/partials/queue-content.blade.php ENDPATH**/ ?>