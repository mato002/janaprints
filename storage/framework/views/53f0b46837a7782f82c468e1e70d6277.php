<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $hubUrl = WorkspaceEmbed::url($hubUrl);
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Acquisitions'),'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Acquisitions')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Acquisitions'),'description' => match ($activeTab) {
            'queue' => __('Received asset purchases awaiting capitalization.'),
            'warranties' => __('Asset warranty profiles and expiry tracking.'),
            'reconciliation' => __('Procurement, accounting, and asset register alignment.'),
            default => __('Procurement-to-asset capitalization overview.'),
        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Acquisitions')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($activeTab) {
            'queue' => __('Received asset purchases awaiting capitalization.'),
            'warranties' => __('Asset warranty profiles and expiry tracking.'),
            'reconciliation' => __('Procurement, accounting, and asset register alignment.'),
            default => __('Procurement-to-asset capitalization overview.'),
        })]); ?>
        <?php if (! (WorkspaceEmbed::inWorkspaceContext())): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <?php if($activeTab === 'reconciliation'): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assets.reconciliation.view')): ?>
                        <form method="POST" action="<?php echo e(route('admin.assets.acquisitions.reconciliation.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="erp-btn-primary"><?php echo e(__('Run reconciliation')); ?></button>
                        </form>
                    <?php endif; ?>
                <?php elseif(in_array($activeTab, ['overview', 'queue'], true)): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('capitalize', \App\Models\Assets\AssetCapitalizationCandidate::class)): ?>
                        <a href="<?php echo e(WorkspaceEmbed::url($hubUrl . '?tab=queue')); ?>" class="erp-btn-primary" data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"><?php echo e(__('Capitalization queue')); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
             <?php $__env->endSlot(); ?>
        <?php endif; ?>
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

    <?php echo $__env->make('admin.assets.acquisitions.partials.tabs-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('admin.assets.acquisitions.partials.tabs.' . str_replace('-', '_', $activeTab), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\acquisitions\hub.blade.php ENDPATH**/ ?>