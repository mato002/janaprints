<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $quotation->quotation_number,'breadcrumbs' => [['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => $quotation->quotation_number]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $quotation->quotation_number,'description' => $quotation->customer?->company_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->quotation_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->customer?->company_name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="erp-badge"><?php echo e(str_replace('_', ' ', $quotation->status->value)); ?></span>
            <span class="text-sm text-slate-500">Rev <?php echo e($quotation->revision_number); ?></span>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $quotation)): ?>
                <a href="<?php echo e(route('admin.quotations.document', $quotation)); ?>" class="erp-btn-secondary"><?php echo e(__('View document')); ?></a>
                <?php if (isset($component)) { $__componentOriginal3c4886a9ff00288f144ef8192d533805 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4886a9ff00288f144ef8192d533805 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.documents.pdf-download-button','data' => ['url' => route('admin.quotations.document.pdf', $quotation),'filename' => $quotation->quotation_number,'class' => 'erp-btn-secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('documents.pdf-download-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.quotations.document.pdf', $quotation)),'filename' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->quotation_number),'class' => 'erp-btn-secondary']); ?>
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
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $quotation)): ?>
                <a href="<?php echo e(route('admin.quotations.edit', $quotation)); ?>" class="erp-btn-secondary"><?php echo e(__('Edit')); ?></a>
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

    <div class="workspace-kpi-grid grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Subtotal'),'value' => $quotation->currency.' '.number_format($quotation->subtotal, 2),'icon' => 'currency-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subtotal')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->currency.' '.number_format($quotation->subtotal, 2)),'icon' => 'currency-dollar']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Tax'),'value' => $quotation->currency.' '.number_format($quotation->tax_amount, 2),'icon' => 'receipt-tax']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tax')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->currency.' '.number_format($quotation->tax_amount, 2)),'icon' => 'receipt-tax']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Total'),'value' => $quotation->currency.' '.number_format($quotation->total_amount, 2),'icon' => 'calculator']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->currency.' '.number_format($quotation->total_amount, 2)),'icon' => 'calculator']); ?>
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

    <?php echo $__env->make('admin.sales.quotations.partials.printing-intelligence-estimate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('admin.sales.quotations.partials.artwork-link', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
        <div class="workspace-action-bar flex flex-wrap gap-2">
            <?php if($quotation->status === App\Enums\QuotationStatus::Draft): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $quotation)): ?>
                    <form method="POST" action="<?php echo e(route('admin.quotations.submit-approval', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary"><?php echo e(__('Submit for approval')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($quotation->status === App\Enums\QuotationStatus::PendingApproval): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $quotation)): ?>
                    <form method="POST" action="<?php echo e(route('admin.quotations.approve', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Approve & send')); ?></button></form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send', $quotation)): ?>
                    <form method="POST" action="<?php echo e(route('admin.quotations.send', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary"><?php echo e(__('Send')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($quotation->status === App\Enums\QuotationStatus::Sent): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $quotation)): ?>
                    <form method="POST" action="<?php echo e(route('admin.quotations.mark-viewed', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary"><?php echo e(__('Mark viewed')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($quotation->status === App\Enums\QuotationStatus::Viewed): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $quotation)): ?>
                    <form method="POST" action="<?php echo e(route('admin.quotations.accept', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-primary"><?php echo e(__('Accept')); ?></button></form>
                    <form method="POST" action="<?php echo e(route('admin.quotations.reject', $quotation)); ?>"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary text-red-600"><?php echo e(__('Reject')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($quotation->status === App\Enums\QuotationStatus::Accepted): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('convert', $quotation)): ?>
                    <a
                        href="<?php echo e(route('admin.sales-orders.create', ['quotation_id' => $quotation->id, 'tab' => 'quotation', 'customer_id' => $quotation->customer_id])); ?>"
                        class="erp-btn-primary"
                        data-turbo-frame="erp-form-modal"
                    ><?php echo e(__('Convert to sales order')); ?></a>
                    <form method="POST" action="<?php echo e(route('admin.quotations.convert', $quotation)); ?>" class="inline"><?php echo csrf_field(); ?>
                        <button class="erp-btn-secondary"><?php echo e(__('Quick convert')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($quotation->salesOrder): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $quotation->salesOrder)): ?>
                    <a href="<?php echo e(route('admin.sales-orders.show', $quotation->salesOrder)); ?>" class="erp-btn-secondary"><?php echo e(__('View sales order')); ?></a>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Line items')); ?></h3>
            <table class="erp-table text-sm">
                <thead><tr><th><?php echo e(__('Item')); ?></th><th><?php echo e(__('Qty')); ?></th><th><?php echo e(__('Total')); ?></th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($item->item_name); ?></td>
                            <td><?php echo e($item->quantity); ?></td>
                            <td><?php echo e(number_format($item->line_total, 2)); ?></td>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Revision history')); ?></h3>
            <?php $__currentLoopData = $quotation->revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $revision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm border-b py-2">
                    Rev <?php echo e($revision->revision_number); ?> — <?php echo e($revision->created_at); ?>

                    <span class="text-slate-400">(<?php echo e($revision->creator?->name); ?>)</span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <h3 class="font-medium mb-3"><?php echo e(__('Notes')); ?></h3>
            <?php $__currentLoopData = $quotation->quotationNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p class="text-sm border-b py-2"><?php echo e($note->note); ?> <span class="text-xs text-slate-400"><?php echo e($note->user?->name); ?></span></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <form method="POST" action="<?php echo e(route('admin.quotations.notes.store', $quotation)); ?>" class="mt-3"><?php echo csrf_field(); ?>
                <textarea name="note" class="erp-input" rows="2" required></textarea>
                <button class="erp-btn-secondary mt-2 text-sm"><?php echo e(__('Add note')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\quotations\show.blade.php ENDPATH**/ ?>