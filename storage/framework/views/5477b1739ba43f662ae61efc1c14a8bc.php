<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Vendors'),'breadcrumbs' => [['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Vendor Management')], ['label' => __('Vendors')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.procurement.partials.desk-mode-nav', ['activeProcurementView' => \App\Support\Procurement\ProcurementDeskViews::SUPPLIERS], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Vendors'),'description' => __('Supplier and vendor master data.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Vendors')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Supplier and vendor master data.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Procurement\Vendor::class)): ?>
                <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => route('admin.procurement.vendors.create')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.procurement.vendors.create'))]); ?><?php echo e(__('Create vendor')); ?> <?php echo $__env->renderComponent(); ?>
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

    <?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['searchPlaceholder' => __('Search vendors…'),'exportRoute' => 'admin.procurement.exports','exportRouteParams' => ['listing' => 'vendors'],'exportQuery' => request()->query(),'formatInPath' => true,'exportFilename' => 'vendors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search vendors…')),'export-route' => 'admin.procurement.exports','export-route-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['listing' => 'vendors']),'export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query()),'format-in-path' => true,'export-filename' => 'vendors']); ?>
         <?php $__env->slot('head', null, []); ?> 
            <tr>
                <th scope="col"><?php echo e(__('Vendor')); ?></th>
                <th scope="col" class="hidden md:table-cell"><?php echo e(__('Type')); ?></th>
                <th scope="col"><?php echo e(__('Status')); ?></th>
                <th scope="col" class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
            </tr>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('body', null, []); ?> 
            <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr x-show="rowVisible(<?php echo \Illuminate\Support\Js::from(strtolower($vendor->vendor_code.' '.$vendor->vendor_name.' '.$vendor->vendor_type->value.' '.$vendor->status->value))->toHtml() ?>)">
                    <td>
                        <div class="font-medium"><?php echo e($vendor->vendor_name); ?></div>
                        <div class="font-mono text-[11px] text-slate-500"><?php echo e($vendor->vendor_code); ?></div>
                    </td>
                    <td class="hidden md:table-cell"><?php echo e(str($vendor->vendor_type->value)->headline()); ?></td>
                    <td><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $vendor->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vendor->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></td>
                    <td class="erp-table-actions-col">
                        <?php if (isset($component)) { $__componentOriginalb5a89013017505cf4d4d69115d724d07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5a89013017505cf4d4d69115d724d07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.procurement.vendors.show', $vendor)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.procurement.vendors.show', $vendor))]); ?><?php echo e(__('View')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $vendor)): ?>
                                <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.procurement.vendors.edit', $vendor),'dataErpModalOpen' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.procurement.vendors.edit', $vendor)),'data-erp-modal-open' => true]); ?><?php echo e(__('Edit')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                            <?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $attributes = $__attributesOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__attributesOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $component = $__componentOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__componentOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'truck','title' => __('No vendors yet')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'truck','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No vendors yet'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td></tr>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('footer', null, []); ?> <?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $vendors]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vendors)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $attributes = $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $component = $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\vendors\index.blade.php ENDPATH**/ ?>