<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $invoice->invoice_number,'breadcrumbs' => [['label' => __('Invoices'), 'url' => route('admin.invoices.index')], ['label' => $invoice->invoice_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $invoice->invoice_number,'description' => $invoice->customer?->company_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->invoice_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->customer?->company_name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => match($invoice->status) {
                App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                App\Enums\CustomerInvoiceStatus::Approved => 'info',
                App\Enums\CustomerInvoiceStatus::Posted => 'success',
                App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
            }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($invoice->status) {
                App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                App\Enums\CustomerInvoiceStatus::Approved => 'info',
                App\Enums\CustomerInvoiceStatus::Posted => 'success',
                App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
            })]); ?><?php echo e($invoice->status->label()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
            <span class="erp-badge"><?php echo e($invoice->invoice_type->label()); ?></span>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $invoice)): ?>
                <a href="<?php echo e(route('admin.invoices.document', $invoice)); ?>" class="erp-btn-secondary"><?php echo e(__('View document')); ?></a>
                <?php if (isset($component)) { $__componentOriginal3c4886a9ff00288f144ef8192d533805 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4886a9ff00288f144ef8192d533805 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.documents.pdf-download-button','data' => ['url' => route('admin.invoices.document.pdf', $invoice),'filename' => $invoice->invoice_number,'class' => 'erp-btn-secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('documents.pdf-download-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.invoices.document.pdf', $invoice)),'filename' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->invoice_number),'class' => 'erp-btn-secondary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $attributes = $__attributesOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__attributesOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c4886a9ff00288f144ef8192d533805)): ?>
<?php $component = $__componentOriginal3c4886a9ff00288f144ef8192d533805; ?>
<?php unset($__componentOriginal3c4886a9ff00288f144ef8192d533805); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('emailInvoice', $invoice)): ?>
                <?php if (isset($component)) { $__componentOriginal125d5b02f1b34361090390749fc44ac6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal125d5b02f1b34361090390749fc44ac6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.documents.email-submit-form','data' => ['action' => route('admin.invoices.email', $invoice),'label' => __('Email invoice'),'submittingLabel' => __('Sending email…'),'submittingMessage' => filled($invoice->customer?->email)
                        ? __('Sending invoice to :recipient…', ['recipient' => $invoice->customer->email])
                        : __('Sending invoice…')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('documents.email-submit-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.invoices.email', $invoice)),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email invoice')),'submitting-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sending email…')),'submitting-message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($invoice->customer?->email)
                        ? __('Sending invoice to :recipient…', ['recipient' => $invoice->customer->email])
                        : __('Sending invoice…'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal125d5b02f1b34361090390749fc44ac6)): ?>
<?php $attributes = $__attributesOriginal125d5b02f1b34361090390749fc44ac6; ?>
<?php unset($__attributesOriginal125d5b02f1b34361090390749fc44ac6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal125d5b02f1b34361090390749fc44ac6)): ?>
<?php $component = $__componentOriginal125d5b02f1b34361090390749fc44ac6; ?>
<?php unset($__componentOriginal125d5b02f1b34361090390749fc44ac6); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $invoice)): ?>
                <a href="<?php echo e(route('admin.invoices.edit', $invoice)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
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

    <div class="workspace-kpi-grid mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Subtotal'),'value' => number_format($invoice->subtotal, 2),'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subtotal')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($invoice->subtotal, 2)),'icon' => 'currency-dollar']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Tax'),'value' => number_format($invoice->tax_amount, 2),'icon' => 'receipt-tax']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tax')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($invoice->tax_amount, 2)),'icon' => 'receipt-tax']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Total'),'value' => number_format($invoice->total_amount, 2),'icon' => 'calculator']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($invoice->total_amount, 2)),'icon' => 'calculator']); ?>
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
        <?php if($invoice->billing_percent): ?>
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Progress'),'value' => $invoice->billing_percent.'%','icon' => 'chart-bar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Progress')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->billing_percent.'%'),'icon' => 'chart-bar']); ?>
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
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
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
        <div class="workspace-action-bar flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $invoice)): ?>
                <form method="POST" action="<?php echo e(route('admin.invoices.approve', $invoice)); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Approve')); ?></button></form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('post', $invoice)): ?>
                <form method="POST" action="<?php echo e(route('admin.invoices.post', $invoice)); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Post to AR')); ?></button></form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('creditNote', $invoice)): ?>
                <form method="POST" action="<?php echo e(route('admin.invoices.credit-note.store', $invoice)); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary"><?php echo e(__('Credit note')); ?></button></form>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $invoice)): ?>
                <form method="POST" action="<?php echo e(route('admin.invoices.cancel', $invoice)); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="erp-btn-secondary text-red-600"><?php echo e(__('Cancel')); ?></button></form>
            <?php endif; ?>
            <?php if($invoice->status === App\Enums\CustomerInvoiceStatus::Posted && $invoice->balance_due > 0): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerPayment::class)): ?>
                    <a href="<?php echo e(route('admin.payments.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id])); ?>" class="erp-btn-primary"><?php echo e(__('Record payment')); ?></a>
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

    <?php if($invoice->status === App\Enums\CustomerInvoiceStatus::Posted): ?>
        <div class="workspace-kpi-grid mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Paid'),'value' => number_format($invoice->amount_paid, 2),'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Paid')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($invoice->amount_paid, 2)),'icon' => 'currency-dollar']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Balance due'),'value' => number_format($invoice->balance_due, 2),'icon' => 'scale']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Balance due')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($invoice->balance_due, 2)),'icon' => 'scale']); ?>
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
    <?php endif; ?>

    <?php
        $postedPayments = $invoice->paymentAllocations
            ->map(fn ($allocation) => $allocation->payment)
            ->filter(fn ($payment) => $payment && $payment->status === App\Enums\CustomerPaymentStatus::Posted)
            ->unique('id');
    ?>
    <?php if($postedPayments->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
            <h3 class="font-medium mb-3"><?php echo e(__('Payment receipts')); ?></h3>
            <ul class="text-sm space-y-1">
                <?php $__currentLoopData = $postedPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex flex-wrap items-center gap-2">
                        <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" class="font-mono text-erp-accent"><?php echo e($payment->payment_number); ?></a>
                        <span class="text-slate-500"><?php echo e(number_format($payment->amount, 2)); ?></span>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewReceipt', $payment)): ?>
                            <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>" class="text-erp-accent text-xs"><?php echo e(__('View receipt')); ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
            <h3 class="font-medium mb-3"><?php echo e(__('References')); ?></h3>
            <dl class="workspace-meta-grid text-sm space-y-2">
                <?php if($invoice->salesOrder): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Sales order')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.sales-orders.show', $invoice->salesOrder)); ?>" class="text-erp-accent"><?php echo e($invoice->salesOrder->order_number); ?></a></dd></div>
                <?php endif; ?>
                <?php if($invoice->jobCard): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Job card')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.production.job-cards.show', $invoice->jobCard)); ?>" class="text-erp-accent"><?php echo e($invoice->jobCard->job_card_number); ?></a></dd></div>
                <?php endif; ?>
                <?php if($invoice->creditedInvoice): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Credits')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.invoices.show', $invoice->creditedInvoice)); ?>" class="text-erp-accent"><?php echo e($invoice->creditedInvoice->invoice_number); ?></a></dd></div>
                <?php endif; ?>
                <?php if($invoice->postedJournal): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('GL journal')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.accounting.journals.show', $invoice->postedJournal)); ?>" class="text-erp-accent"><?php echo e($invoice->postedJournal->journal_number); ?></a></dd></div>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Tax summary')); ?></h3>
            <?php $__empty_1 = true; $__currentLoopData = $invoice->taxLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between text-sm py-1 border-b border-erp-border">
                    <span><?php echo e($tax->tax_name); ?></span>
                    <span class="font-mono"><?php echo e(number_format($tax->tax_amount, 2)); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500"><?php echo e(__('No tax lines')); ?></p>
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
        <h3 class="font-medium mb-3"><?php echo e(__('Lines')); ?></h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th><?php echo e(__('Item')); ?></th><th><?php echo e(__('Qty')); ?></th><th><?php echo e(__('Price')); ?></th><th><?php echo e(__('Tax %')); ?></th><th><?php echo e(__('Total')); ?></th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $invoice->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-t border-erp-border">
                        <td class="py-2"><?php echo e($line->item_name); ?></td>
                        <td class="py-2"><?php echo e($line->quantity); ?></td>
                        <td class="py-2 font-mono"><?php echo e(number_format($line->unit_price, 2)); ?></td>
                        <td class="py-2"><?php echo e($line->tax_rate); ?>%</td>
                        <td class="py-2 font-mono"><?php echo e(number_format($line->line_total, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/invoices/show.blade.php ENDPATH**/ ?>