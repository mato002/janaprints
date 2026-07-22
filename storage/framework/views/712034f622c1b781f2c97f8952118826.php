<?php
    $spec = $specification ?? null;
    $isEdit = (bool) $spec;
    $prefill = fn (string $field, mixed $default = null) => old(
        $field,
        $spec?->{$field}
            ?? ($templateDefaults[$field] ?? null)
            ?? $default
    );
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $isEdit ? __('Edit production specification') : __('Production specification'),'breadcrumbs' => [
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => $isEdit ? __('Edit specification') : __('Add specification')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $isEdit ? __('Edit production specification') : __('Production specification'),'description' => $salesOrderItem->item_name . ' × ' . $salesOrderItem->quantity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? __('Edit production specification') : __('Production specification')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrderItem->item_name . ' × ' . $salesOrderItem->quantity)]); ?>
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

    <form
        method="POST"
        action="<?php echo e($isEdit
            ? route('admin.sales-orders.items.specification.update', [$salesOrder, $salesOrderItem, $spec])
            : route('admin.sales-orders.items.specification.store', [$salesOrder, $salesOrderItem])); ?>"
        class="space-y-6"
    >
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        <?php echo $__env->make('admin.production.specifications.partials.form-fields', [
            'specification' => $spec,
            'templateDefaults' => $templateDefaults ?? [],
            'prefill' => $prefill,
            'printTemplates' => $printTemplates ?? collect(),
            'selectedTemplateId' => $selectedTemplateId ?? null,
            'productionTypes' => $productionTypes,
            'inkTypes' => $inkTypes,
            'approvalStatuses' => $approvalStatuses,
            'paperItems' => $paperItems,
            'materialItems' => $materialItems,
            'inkProfiles' => $inkProfiles,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Save specification')); ?></button>
            <a href="<?php echo e(route('admin.sales-orders.show', $salesOrder)); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\specifications\create.blade.php ENDPATH**/ ?>