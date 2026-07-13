<?php
    $activeOnPage = $customers->filter(fn ($c) => $c->status->value === 'active')->count();
    $inactiveOnPage = $customers->filter(fn ($c) => $c->status->value === 'inactive')->count();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Customers'),'breadcrumbs' => [['label' => __('CRM')], ['label' => __('Customers')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="crm-customers w-full min-w-0 space-y-4">
        <header class="crm-customers__header">
            <div class="crm-customers__header-main">
                <div>
                    <h1 class="crm-customers__title"><?php echo e(__('Customers')); ?></h1>
                    <p class="crm-customers__subtitle"><?php echo e(__('Customer accounts, contacts, and communication history.')); ?></p>
                </div>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Crm\Customer::class)): ?>
                    <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => route('admin.crm.customers.create'),'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.create')),'class' => 'shrink-0']); ?><?php echo e(__('Create customer')); ?> <?php echo $__env->renderComponent(); ?>
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
            </div>
        </header>

        <section class="crm-customers__kpi-strip" aria-label="<?php echo e(__('Customer list summary')); ?>">
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label"><?php echo e(__('Total customers')); ?></span>
                <span class="crm-customers__kpi-value"><?php echo e(number_format($customers->total())); ?></span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label"><?php echo e(__('On this page')); ?></span>
                <span class="crm-customers__kpi-value"><?php echo e($customers->count()); ?></span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label"><?php echo e(__('Active (page)')); ?></span>
                <span class="crm-customers__kpi-value"><?php echo e($activeOnPage); ?></span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label"><?php echo e(__('Inactive (page)')); ?></span>
                <span class="crm-customers__kpi-value"><?php echo e($inactiveOnPage); ?></span>
            </div>
        </section>

        <p class="crm-customers__hint">
            <span aria-hidden="true">↗</span>
            <?php echo e(__('Click any row to open the customer 360 workspace.')); ?>

        </p>

        <?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => ['class' => 'crm-customers__data-grid','searchPlaceholder' => __('Search customers…'),'exportRoute' => 'admin.crm.customers.export','exportQuery' => request()->query(),'formatInPath' => true,'exportFilename' => 'customers','chips' => [
                ['id' => 'all', 'label' => __('All')],
                ['id' => 'active', 'label' => __('Active')],
                ['id' => 'inactive', 'label' => __('Inactive')],
            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'crm-customers__data-grid','search-placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Search customers…')),'export-route' => 'admin.crm.customers.export','export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query()),'format-in-path' => true,'export-filename' => 'customers','chips' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['id' => 'all', 'label' => __('All')],
                ['id' => 'active', 'label' => __('Active')],
                ['id' => 'inactive', 'label' => __('Inactive')],
            ])]); ?>
             <?php $__env->slot('head', null, []); ?> 
                <tr>
                    <th scope="col"><?php echo e(__('Customer')); ?></th>
                    <th scope="col" class="hidden md:table-cell"><?php echo e(__('Branch')); ?></th>
                    <th scope="col" class="hidden lg:table-cell"><?php echo e(__('Contact')); ?></th>
                    <th scope="col"><?php echo e(__('Status')); ?></th>
                    <th scope="col" class="erp-table-actions-col"><span class="sr-only"><?php echo e(__('Actions')); ?></span></th>
                </tr>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('body', null, []); ?> 
                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $search = strtolower($customer->customer_code.' '.$customer->company_name.' '.($customer->branch?->name ?? '').' '.$customer->status->value.' '.($customer->phone ?? '').' '.($customer->email ?? ''));
                        $chip = strtolower($customer->status->value);
                        $initial = mb_strtoupper(mb_substr($customer->company_name, 0, 1));
                        $showUrl = route('admin.crm.customers.show', $customer);
                        $statusClass = match ($customer->status->value) {
                            'active' => 'crm-customers__status--active',
                            'inactive' => 'crm-customers__status--inactive',
                            default => 'crm-customers__status--prospect',
                        };
                    ?>
                    <tr
                        x-show="rowVisible(<?php echo \Illuminate\Support\Js::from($search)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($chip)->toHtml() ?>)"
                        class="crm-customers__row group"
                        data-href="<?php echo e($showUrl); ?>"
                        data-turbo-frame="erp-main"
                        role="link"
                        tabindex="0"
                        aria-label="<?php echo e(__('Open :name', ['name' => $customer->company_name])); ?>"
                        @click="if (!$event.target.closest('[data-erp-row-actions]')) { const url = $el.dataset.href; if (window.Turbo) { window.Turbo.visit(url, { frame: $el.dataset.turboFrame || 'erp-main' }); } else { window.location.href = url; } }"
                        @keydown.enter.prevent="if (!$event.target.closest('[data-erp-row-actions]')) { $el.click(); }"
                    >
                        <td class="crm-customers__cell-customer">
                            <div class="flex items-center gap-3">
                                <div class="crm-customers__avatar" aria-hidden="true"><?php echo e($initial); ?></div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="truncate font-semibold text-slate-900 group-hover:text-indigo-800"><?php echo e($customer->company_name); ?></div>
                                        <?php if($customer->portalUser): ?>
                                            <span class="crm-customers__portal-badge"><?php echo e(__('Portal')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="font-mono text-[11px] text-slate-500"><?php echo e($customer->customer_code); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell text-slate-600"><?php echo e($customer->branch?->name ?? '—'); ?></td>
                        <td class="hidden lg:table-cell">
                            <div class="min-w-0 text-sm text-slate-600">
                                <?php if($customer->phone): ?>
                                    <p class="truncate"><?php echo e($customer->phone); ?></p>
                                <?php endif; ?>
                                <?php if($customer->email): ?>
                                    <p class="truncate text-xs text-slate-500"><?php echo e($customer->email); ?></p>
                                <?php endif; ?>
                                <?php if(! $customer->phone && ! $customer->email): ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="crm-customers__status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($customer->status->value)); ?></span>
                        </td>
                        <td class="erp-table-actions-col" @click.stop>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => $showUrl,'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showUrl),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('View 360')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['action' => route('admin.communications.inbox.customers.start', $customer),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.inbox.customers.start', $customer)),'method' => 'POST']); ?><?php echo e(__('Start conversation')); ?> <?php echo $__env->renderComponent(); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.crm.customers.edit', $customer),'dataErpModalOpen' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'data-erp-modal-open' => true]); ?><?php echo e(__('Edit')); ?> <?php echo $__env->renderComponent(); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('deactivate', $customer)): ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['action' => route('admin.crm.customers.deactivate', $customer),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.deactivate', $customer)),'method' => 'POST']); ?><?php echo e(__('Deactivate')); ?> <?php echo $__env->renderComponent(); ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $customer)): ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['action' => route('admin.crm.customers.destroy', $customer),'method' => 'DELETE','confirm' => __('Delete this customer permanently?')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.destroy', $customer)),'method' => 'DELETE','confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Delete this customer permanently?'))]); ?><?php echo e(__('Remove')); ?> <?php echo $__env->renderComponent(); ?>
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
                    <tr>
                        <td colspan="5">
                            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'user-circle','title' => __('No customers yet'),'description' => __('Start by adding your first customer account.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-circle','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No customers yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start by adding your first customer account.'))]); ?>
                                 <?php $__env->slot('action', null, []); ?> 
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Crm\Customer::class)): ?>
                                        <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => route('admin.crm.customers.create')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.create'))]); ?><?php echo e(__('Create customer')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('footer', null, []); ?> <?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $customers]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers)]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/index.blade.php ENDPATH**/ ?>