<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $salesOrder->order_number,'breadcrumbs' => [['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => $salesOrder->order_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $salesOrder->order_number,'description' => $salesOrder->customer?->company_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrder->order_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesOrder->customer?->company_name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="erp-badge"><?php echo e(str_replace('_', ' ', $salesOrder->status->value)); ?></span>
            <?php if(! empty($financial)): ?>
                <span class="erp-badge bg-<?php echo e($financial['financial_status_variant'] === 'success' ? 'emerald' : ($financial['financial_status_variant'] === 'warning' ? 'amber' : 'slate')); ?>-100">
                    <?php echo e($financial['financial_status_label']); ?>

                </span>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $salesOrder)): ?>
                <a href="<?php echo e(route('admin.sales-orders.edit', $salesOrder)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
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

<div class="workspace-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Subtotal'),'value' => number_format($salesOrder->subtotal, 2),'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subtotal')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($salesOrder->subtotal, 2)),'icon' => 'currency-dollar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Tax'),'value' => number_format($salesOrder->tax_amount, 2),'icon' => 'receipt-tax']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tax')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($salesOrder->tax_amount, 2)),'icon' => 'receipt-tax']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Total'),'value' => number_format($salesOrder->total_amount, 2),'icon' => 'calculator']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($salesOrder->total_amount, 2)),'icon' => 'calculator']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
    </div>

    <?php if(! empty($profitability)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
            <h3 class="font-medium mb-3"><?php echo e(__('Estimated profitability')); ?></h3>
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div><dt class="text-slate-500"><?php echo e(__('Revenue')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['revenue'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Material cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['material_cost'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Waste cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['wastage_cost'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Outsource cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['outsource_cost'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Estimated profit')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['estimated_profit'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Margin')); ?></dt><dd><?php echo e(number_format($profitability['estimated_margin_percent'], 1)); ?>%</dd></div>
            </dl>
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
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <h3 class="font-medium mb-3"><?php echo e(__('Workflow')); ?></h3>
        <?php if (isset($component)) { $__componentOriginalf5ffa9581a76bd6f6146407ee4444540 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workflow-error','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workflow-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $attributes = $__attributesOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__attributesOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540)): ?>
<?php $component = $__componentOriginalf5ffa9581a76bd6f6146407ee4444540; ?>
<?php unset($__componentOriginalf5ffa9581a76bd6f6146407ee4444540); ?>
<?php endif; ?>

        <ol class="mb-4 flex flex-wrap gap-2">
            <?php $__currentLoopData = $workflow['pipeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'rounded-full px-3 py-1 text-xs font-medium',
                    'bg-emerald-100 text-emerald-800' => $step['state'] === 'complete',
                    'bg-erp-accent/10 text-erp-accent' => $step['state'] === 'current',
                    'bg-slate-100 text-slate-500' => in_array($step['state'], ['upcoming', 'paused'], true),
                    'bg-red-100 text-red-700' => $step['state'] === 'cancelled',
                ]); ?>"><?php echo e($step['label']); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>

        <?php if($workflow['hint']): ?>
            <p class="mb-3 text-sm text-slate-600"><?php echo e($workflow['hint']); ?></p>
        <?php endif; ?>

        <?php if($salesOrder->jobCard): ?>
            <p class="mb-3 text-sm">
                <a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="text-erp-accent hover:text-erp-accent-hover">
                    <?php echo e(__('Open job card :number', ['number' => $salesOrder->jobCard->job_card_number])); ?>

                </a>
            </p>
        <?php endif; ?>

        <div class="workspace-action-bar flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('confirm', $salesOrder)): ?>
                <?php if($workflow['can_confirm']): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.confirm', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Confirm order')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production', $salesOrder)): ?>
                <?php if($workflow['can_release']): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.release-to-production', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Send to production')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('close', $salesOrder)): ?>
                <?php if($workflow['can_close']): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.close', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Close order')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $salesOrder)): ?>
                <?php if($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::OnHold)): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.hold', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary"><?php echo e(__('On hold')); ?></button></form>
                <?php endif; ?>
                <?php if($salesOrder->status === App\Enums\SalesOrderStatus::OnHold): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.resume', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Resume')); ?></button></form>
                <?php endif; ?>
                <?php if($salesOrder->status->canTransitionTo(App\Enums\SalesOrderStatus::Cancelled)): ?>
                    <form method="POST" action="<?php echo e(route('admin.sales-orders.cancel', $salesOrder)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary text-red-600"><?php echo e(__('Cancel')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h3 class="font-medium"><?php echo e(__('Invoicing')); ?></h3>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerInvoice::class)): ?>
                <?php if($salesOrder->remainingInvoiceTotal() > 0 && !in_array($salesOrder->status, [App\Enums\SalesOrderStatus::Draft, App\Enums\SalesOrderStatus::Cancelled])): ?>
                    <a href="<?php echo e(route('admin.invoices.from-sales-order', $salesOrder)); ?>" class="erp-btn-primary" data-turbo-frame="_top"><?php echo e(__('Create invoice')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if(! empty($financial['billing_eligibility']['blockers'])): ?>
            <p class="mb-3 text-sm text-amber-700"><?php echo e(implode(' ', $financial['billing_eligibility']['blockers'])); ?></p>
            <p class="mb-3 text-xs text-amber-700"><?php echo e(__('Use deposit or progress billing on the next screen if you need to invoice before fulfilment is complete.')); ?></p>
        <?php endif; ?>
        <dl class="workspace-meta-grid text-sm grid sm:grid-cols-3 gap-3 mb-4">
            <div><dt class="text-slate-500"><?php echo e(__('Order total')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Invoiced')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->invoiced_total, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Remaining')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->remainingInvoiceTotal(), 2)); ?></dd></div>
        </dl>
        <?php if(! empty($financial['payment'])): ?>
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-4 gap-3 mb-4 border-t border-erp-border pt-3">
                <div><dt class="text-slate-500"><?php echo e(__('Payment status')); ?></dt><dd><?php echo e($financial['payment']['label']); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Paid')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_paid'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Outstanding')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_outstanding'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Fulfilment ready')); ?></dt><dd><?php echo e(($financial['billing_eligibility']['fulfilment_ready'] ?? false) ? __('Yes') : __('No')); ?></dd></div>
            </dl>
        <?php endif; ?>
        <?php if(! empty($financial['deposit'])): ?>
            <dl class="workspace-meta-grid text-sm grid sm:grid-cols-4 gap-3 border-t border-erp-border pt-3">
                <div><dt class="text-slate-500"><?php echo e(__('Billing type')); ?></dt><dd><?php echo e($financial['deposit']['billing_type']); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Required deposit')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['required'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Deposit invoiced')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['invoiced'], 2)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Deposit paid')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['paid'], 2)); ?></dd></div>
            </dl>
        <?php endif; ?>
        <?php if($salesOrder->invoices->isNotEmpty()): ?>
            <ul class="mt-3 text-sm space-y-1">
                <?php $__currentLoopData = $salesOrder->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><a href="<?php echo e(route('admin.invoices.show', $inv)); ?>" class="text-erp-accent font-mono"><?php echo e($inv->invoice_number); ?></a> — <?php echo e($inv->status->label()); ?> (<?php echo e(number_format($inv->total_amount, 2)); ?>)</li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
            <h3 class="font-medium mb-3"><?php echo e(__('Traceability')); ?></h3>
            <dl class="workspace-meta-grid text-sm space-y-2">
                <div><dt class="text-slate-500"><?php echo e(__('Quotation')); ?></dt><dd><?php echo e($salesOrder->quotation?->quotation_number); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Artwork')); ?></dt><dd><?php echo e($salesOrder->artworkRequest?->request_number); ?></dd></div>
                <?php if($salesOrder->jobCard): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Job card')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.production.job-cards.show', $salesOrder->jobCard)); ?>" class="text-erp-accent hover:text-erp-accent-hover"><?php echo e($salesOrder->jobCard->job_card_number); ?></a></dd></div>
                <?php endif; ?>
                <?php if($salesOrder->conversion): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Converted')); ?></dt>
                        <dd><?php echo e($salesOrder->conversion->created_at?->format('Y-m-d H:i')); ?> — <?php echo e($salesOrder->conversion->converter?->name); ?>

                            (<?php echo e(__('Quotation rev')); ?> <?php echo e($salesOrder->conversion->quotation_revision_number); ?>,
                            <?php echo e(__('Artwork v')); ?><?php echo e($salesOrder->conversion->artwork_version_number); ?>)</dd></div>
                <?php endif; ?>
            </dl>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Production product')); ?></h3>
            <?php if($salesOrder->inventoryItem): ?>
                <p class="mb-3 text-sm">
                    <span class="font-medium"><?php echo e($salesOrder->inventoryItem->item_name); ?></span>
                    <span class="text-slate-500">(<?php echo e($salesOrder->inventoryItem->sku); ?>)</span>
                </p>
            <?php else: ?>
                <p class="mb-3 text-sm text-amber-700"><?php echo e(__('No catalogue product linked yet. Link a finished-good inventory item so production and material requirements can run.')); ?></p>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('updateProductionSetup', $salesOrder)): ?>
                <form method="POST" action="<?php echo e(route('admin.sales-orders.production-setup.update', $salesOrder)); ?>" class="flex flex-wrap items-end gap-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="min-w-[16rem] flex-1">
                        <label class="erp-label"><?php echo e(__('Catalogue item')); ?></label>
                        <select name="inventory_item_id" class="erp-input w-full" required>
                            <option value=""><?php echo e(__('Select product')); ?></option>
                            <?php $__currentLoopData = $catalogueItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>" <?php if($salesOrder->inventory_item_id == $item->id): echo 'selected'; endif; ?>>
                                    <?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit" class="erp-btn-secondary"><?php echo e($salesOrder->inventoryItem ? __('Update product') : __('Link product')); ?></button>
                </form>
            <?php endif; ?>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Line items & production specifications')); ?></h3>
            <?php $__empty_1 = true; $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('admin.sales.orders.partials.item-specification', [
                    'salesOrder' => $salesOrder,
                    'item' => $item,
                    'itemSpecifications' => $itemSpecifications ?? [],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No line items.')); ?></p>
            <?php endif; ?>
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
            <h3 class="font-medium mb-3"><?php echo e(__('Notes')); ?></h3>
            <?php $__currentLoopData = $salesOrder->orderNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm py-1"><?php echo e($note->user?->name); ?>: <?php echo e($note->note); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $salesOrder)): ?>
                <form method="POST" action="<?php echo e(route('admin.sales-orders.notes.store', $salesOrder)); ?>" class="mt-4 space-y-2">
                    <?php echo csrf_field(); ?>
                    <textarea name="note" class="erp-input w-full" rows="2" required></textarea>
                    <button class="erp-btn-secondary"><?php echo e(__('Add note')); ?></button>
                </form>
            <?php endif; ?>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Attachments')); ?></h3>
            <?php $__currentLoopData = $salesOrder->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm py-1"><?php echo e($attachment->original_name); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $salesOrder)): ?>
                <form method="POST" action="<?php echo e(route('admin.sales-orders.attachments.store', $salesOrder)); ?>" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" class="erp-input w-full" required>
                    <button class="erp-btn-secondary mt-2"><?php echo e(__('Upload')); ?></button>
                </form>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\orders\show.blade.php ENDPATH**/ ?>