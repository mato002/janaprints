<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolver = app(\App\Services\Documents\DocumentSettingsService::class);
    $companyId = $company->id;
    $embedded = WorkspaceEmbed::inWorkspaceContext();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $title,'breadcrumbs' => $embedded ? [] : [
        ['label' => __('Administration'), 'url' => route('admin.workspaces.administration')],
        ['label' => __('Commercial Documents'), 'url' => route('admin.workspaces.administration.section', 'commercial-documents')],
        ['label' => $title],
    ],'compactWorkspace' => $embedded] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if(! $embedded): ?>
        <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $title,'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <span class="text-xs text-slate-500"><?php echo e(__('Values fall back to config until saved here.')); ?></span>
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
    <?php else: ?>
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-erp-primary"><?php echo e($title); ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($description); ?></p>
        </div>
    <?php endif; ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <form
            method="POST"
            action="<?php echo e($updateRoute); ?>"
            class="p-6"
            data-document-settings-form
            data-settings-tabs
            data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="mb-6 rounded-lg border border-erp-border bg-erp-page/60 px-4 py-3 text-sm text-slate-600">
                <?php echo e(__('Editing document branding for :company.', ['company' => $company->name])); ?>

                <?php if($embedded): ?>
                    <span class="mt-1 block text-xs text-slate-500"><?php echo e(__('Values fall back to config until saved here.')); ?></span>
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <label class="erp-label" for="settings-search"><?php echo e(__('Search settings')); ?></label>
                <input
                    id="settings-search"
                    type="search"
                    class="erp-input max-w-md"
                    placeholder="<?php echo e(__('Search by label or setting key…')); ?>"
                    data-settings-search
                    autocomplete="off"
                >
            </div>

            <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-4" role="tablist" aria-label="<?php echo e(__('Settings sections')); ?>">
                <?php $__currentLoopData = $adminTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        class="erp-filter-pill"
                        data-settings-tab-trigger="<?php echo e($tabKey); ?>"
                        <?php if($loop->first): ?> data-settings-tab-active <?php endif; ?>
                    >
                        <?php echo e($tabLabel); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php $__currentLoopData = $adminTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <section
                    class="space-y-5"
                    data-settings-tab-panel="<?php echo e($tabKey); ?>"
                    <?php if(! $loop->first): ?> hidden <?php endif; ?>
                >
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e($tabLabel); ?></h2>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <?php $__currentLoopData = $schema; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(($meta['admin_tab'] ?? '') !== $tabKey) continue; ?>
                            <?php echo $__env->make('admin.documents.settings.field', [
                                'key' => $key,
                                'meta' => $meta,
                                'record' => $records->get($key),
                                'resolver' => $resolver,
                                'companyId' => $companyId,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', App\Models\DocumentSetting::class)): ?>
                <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Save Settings')); ?></button>
                    <?php if (! ($embedded)): ?>
                        <a
                            href="<?php echo e(WorkspaceEmbed::url(route('admin.workspaces.administration.section', 'commercial-documents'))); ?>"
                            class="erp-btn-secondary"
                            data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
                        ><?php echo e(__('Back to Commercial Documents')); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\documents\settings\form.blade.php ENDPATH**/ ?>