<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Edit print specification'),'breadcrumbs' => [
        ['label' => __('Customers'), 'url' => route('admin.crm.customers.index')],
        ['label' => $customer->company_name, 'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])],
        ['label' => $specification->name],
    ],'maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Edit print specification')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => __('Customers'), 'url' => route('admin.crm.customers.index')],
        ['label' => $customer->company_name, 'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])],
        ['label' => $specification->name],
    ]),'maxWidth' => '3xl']); ?>
    <?php if (isset($component)) { $__componentOriginal0de0e52b643095bf2659e655794f27e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0de0e52b643095bf2659e655794f27e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-preview-lightbox','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-preview-lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.crm.customers.print-specifications.update', [$customer, $specification]),'method' => 'PUT']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.print-specifications.update', [$customer, $specification])),'method' => 'PUT']); ?>
            <?php if(request('from') === 'sales-desk'): ?>
                <input type="hidden" name="from" value="sales-desk">
            <?php endif; ?>
            <?php echo $__env->make('admin.crm.customers.print-specifications.partials.form', [
                'customer' => $customer,
                'specification' => $specification,
                'serialProfile' => $serialProfile,
                'serialSummary' => $serialSummary,
                'liveReferenceWarnings' => $liveReferenceWarnings ?? [],
                'hasOperationalUsage' => $hasOperationalUsage ?? false,
                'statuses' => $statuses,
                'billingTypes' => $billingTypes,
                'fulfilmentMethods' => $fulfilmentMethods,
                'artworkTypes' => $artworkTypes,
                'showArtworkUpload' => false,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if (isset($component)) { $__componentOriginald865c6e99253c837baa94b9ed23bdb6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-actions','data' => ['class' => 'erp-form-modal__actions--sticky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'erp-form-modal__actions--sticky']); ?>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['class' => 'min-h-[2.75rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'min-h-[2.75rem]']); ?><?php echo e(__('Save changes')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $attributes = $__attributesOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__attributesOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d)): ?>
<?php $component = $__componentOriginald865c6e99253c837baa94b9ed23bdb6d; ?>
<?php unset($__componentOriginald865c6e99253c837baa94b9ed23bdb6d); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>

        <section class="mt-6 rounded-lg border border-erp-border p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Upload new artwork version')); ?></h3>
            <form method="POST" action="<?php echo e(route('admin.crm.customers.print-specifications.artworks.store', [$customer, $specification])); ?>" enctype="multipart/form-data" class="space-y-3">
                <?php echo csrf_field(); ?>
                <?php if(request('from') === 'sales-desk'): ?>
                    <input type="hidden" name="from" value="sales-desk">
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="erp-label"><?php echo e(__('File')); ?></label>
                        <input type="file" name="file" class="erp-input w-full min-h-[2.75rem]" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Type')); ?></label>
                        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'artwork_type','options' => $artworkTypes,'value' => app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->defaultCode(),'createRoute' => 'admin.crm.artwork-types.quick-create','refreshRoute' => 'admin.lookups.artwork_types','permission' => 'crm.customers.update','modalTitle' => __('Create artwork type'),'selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'artwork_type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkTypes),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->defaultCode()),'create-route' => 'admin.crm.artwork-types.quick-create','refresh-route' => 'admin.lookups.artwork_types','permission' => 'crm.customers.update','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create artwork type')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label"><?php echo e(__('Change notes')); ?></label>
                        <input name="change_notes" class="erp-input w-full" maxlength="2000">
                    </div>
                </div>
                <button type="submit" class="erp-btn-secondary min-h-[2.75rem] w-full sm:w-auto"><?php echo e(__('Upload version')); ?></button>
                <p class="text-xs text-slate-500"><?php echo e(__('Versions are never overwritten.')); ?></p>
            </form>
        </section>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $attributes = $__attributesOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__attributesOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $component = $__componentOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__componentOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\edit.blade.php ENDPATH**/ ?>