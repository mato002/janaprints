<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Finance'),'breadcrumbs' => [
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Finance')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Asset Finance'),'description' => match ($activeTab) {
            'runs' => __('Company-wide monthly depreciation execution.'),
            'entries' => __('Posted and draft depreciation register.'),
            'reconciliation' => __('Asset register vs general ledger.'),
            'reports' => __('Register, valuation, and depreciation reports.'),
            'write-offs' => __('Damaged, lost, and obsolete asset write-offs.'),
            default => __('Fixed asset valuation, depreciation, and financial controls.'),
        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Asset Finance')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($activeTab) {
            'runs' => __('Company-wide monthly depreciation execution.'),
            'entries' => __('Posted and draft depreciation register.'),
            'reconciliation' => __('Asset register vs general ledger.'),
            'reports' => __('Register, valuation, and depreciation reports.'),
            'write-offs' => __('Damaged, lost, and obsolete asset write-offs.'),
            default => __('Fixed asset valuation, depreciation, and financial controls.'),
        })]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(in_array($activeTab, ['overview', 'runs'], true)): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('run', \App\Models\Assets\DepreciationRun::class)): ?>
                    <a href="<?php echo e(route('admin.assets.finance.runs.create')); ?>" class="erp-btn-primary"><?php echo e(__('New depreciation run')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($activeTab === 'write-offs'): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage', \App\Models\Assets\AssetWriteOff::class)): ?>
                    <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => route('admin.assets.finance.write-offs.create')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.assets.finance.write-offs.create'))]); ?>
                        <?php echo e(__('New write-off')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($activeTab === 'reconciliation'): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('run', \App\Models\Assets\AssetRegisterReconciliation::class)): ?>
                    <form method="POST" action="<?php echo e(route('admin.assets.finance.reconciliation.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Run reconciliation')); ?></button>
                    </form>
                <?php endif; ?>
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

    <?php echo $__env->make('admin.assets.finance.partials.tabs-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('admin.assets.finance.partials.tabs.' . str_replace('-', '_', $activeTab), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\finance\hub.blade.php ENDPATH**/ ?>