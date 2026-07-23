<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $operatorMode ? __('Designer Desk') : __('Artwork Desk'),'breadcrumbs' => $operatorMode
        ? [['label' => __('Designer Desk')]]
        : [
            ['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')],
            ['label' => __('Designer Desk')],
        ],'compactPage' => false] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="designer-desk-shell"
        x-data="designerDesk(<?php echo \Illuminate\Support\Js::from([
            'panelBase' => url('admin/artwork/desk/requests'),
            'initialRequestKey' => request('request'),
        ])->toHtml() ?>)"
        x-cloak
    >
        <?php if($operatorMode): ?>
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Designer desk')); ?></p>
                    <p class="text-xs text-slate-600"><?php echo e(__('Select a job to work inline — files, specs, and submit actions stay here.')); ?></p>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Designer Desk'),'description' => __('Your operational workspace — accept jobs, upload, and submit without leaving the desk.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Designer Desk')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Your operational workspace — accept jobs, upload, and submit without leaving the desk.'))]); ?>
                 <?php $__env->slot('actions', null, []); ?> 
                    <a href="<?php echo e(route('admin.artwork.dashboard')); ?>" class="erp-btn-secondary" data-turbo-frame="erp-main"><?php echo e(__('Full Artwork dashboard')); ?></a>
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
        <?php endif; ?>

        <?php if(session('status')): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php echo $__env->make('admin.artwork.desk.partials.summary-strip', ['summary' => $summary], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.artwork.desk.partials.urgent-queue', ['urgent' => $urgent], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div :class="selectedKey ? 'opacity-100' : ''">
            <?php echo $__env->make('admin.artwork.desk.partials.table', ['rows' => $rows, 'operatorMode' => $operatorMode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="mt-4 pb-2" x-show="!selectedKey"><?php echo e($requests->links()); ?></div>
        </div>

        <?php echo $__env->make('admin.artwork.desk.partials.workspace', ['operatorMode' => $operatorMode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.artwork.desk.partials.idle-panel', [
            'today_activity' => $today_activity,
            'has_assignments' => $has_assignments,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/index.blade.php ENDPATH**/ ?>