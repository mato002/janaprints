<?php
    $note = $note;
    $step = $note->workflowStep();
    $showInvoiceBlockers = $note->status === \App\Enums\Dispatch\DeliveryNoteStatus::Delivered
        || in_array($step, ['deliver', 'complete'], true);
?>
<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $note->delivery_note_number,'breadcrumbs' => [
    ['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')],
    ['label' => __('Delivery notes'), 'url' => route('admin.dispatch.delivery-notes.index')],
    ['label' => $note->delivery_note_number],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $note->delivery_note_number,'description' => $note->customer?->company_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($note->delivery_note_number),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($note->customer?->company_name)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if($note->productionJobCard): ?>
                <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $note->productionJobCard, 'tab' => 'dispatch'])); ?>" class="erp-btn-secondary"><?php echo e(__('Job dispatch tab')); ?></a>
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

    <div class="mb-4 flex flex-wrap gap-2 text-xs">
        <?php $__currentLoopData = [
            'package' => __('1. Package'),
            'courier' => __('2. Courier / Dispatch'),
            'deliver' => __('3. Deliver / POD'),
            'complete' => __('Complete'),
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'rounded-full px-3 py-1 font-medium',
                'bg-erp-accent text-white' => $step === $key,
                'bg-emerald-100 text-emerald-800' => $step === 'complete' && $key === 'complete',
                'bg-slate-100 text-slate-500' => $step !== $key && ! ($step === 'complete' && in_array($key, ['package', 'courier', 'deliver'], true)),
                'bg-emerald-50 text-emerald-700 line-through' => $step === 'complete' && in_array($key, ['package', 'courier', 'deliver'], true),
            ]); ?>"><?php echo e($label); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
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
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $note->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($note->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Delivery date')); ?></dt><dd><?php echo e($note->delivery_date->format('M j, Y')); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Packages')); ?></dt><dd><?php echo e($note->package_count ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Ready to invoice')); ?></dt><dd><?php echo e($note->invoice_ready ? __('Yes') : __('No')); ?></dd></div>
                <?php if($note->activeInvoice): ?>
                    <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Invoice')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.invoices.show', $note->activeInvoice)); ?>" class="font-mono text-indigo-600"><?php echo e($note->activeInvoice->invoice_number); ?></a></dd>
                    </div>
                <?php elseif($note->invoice_ready && ! ($invoiceEligibility['eligible'] ?? false)): ?>
                    <p class="text-xs text-amber-700"><?php echo e(__('Delivery is complete and billable, but no invoice is linked to this delivery note yet.')); ?></p>
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
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Packaged')); ?></dt><dd><?php echo e($note->packaged_at?->format('M j, Y H:i') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Dispatched')); ?></dt><dd><?php echo e($note->dispatched_at?->format('M j, Y H:i') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Delivered')); ?></dt><dd><?php echo e($note->delivered_at?->format('M j, Y H:i') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Courier')); ?></dt><dd><?php echo e($note->courier_name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Tracking')); ?></dt><dd class="font-mono text-xs"><?php echo e($note->tracking_number ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Recipient')); ?></dt><dd><?php echo e($note->recipient_name ?? '—'); ?></dd></div>
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
            <h3 class="mb-2 text-sm font-semibold"><?php echo e(__('Workflow actions')); ?></h3>
            <div class="space-y-4">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('package', $note)): ?>
                    <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.package', $note)); ?>" class="space-y-2 rounded-lg border border-erp-border p-3">
                        <?php echo csrf_field(); ?>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Package')); ?></p>
                        <input type="number" name="package_count" min="1" value="<?php echo e(old('package_count', $note->package_count ?? 1)); ?>" class="erp-input text-sm" placeholder="<?php echo e(__('Package count')); ?>" required>
                        <textarea name="delivery_address" rows="2" class="erp-input text-sm" placeholder="<?php echo e(__('Delivery address')); ?>"><?php echo e(old('delivery_address', $note->delivery_address ?? $note->dispatch_notes)); ?></textarea>
                        <textarea name="package_notes" rows="2" class="erp-input text-sm" placeholder="<?php echo e(__('Package notes')); ?>"><?php echo e(old('package_notes', $note->package_notes)); ?></textarea>
                        <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Mark packaged')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                    </form>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('dispatch', $note)): ?>
                    <?php echo $__env->make('admin.dispatch.delivery-notes.partials.dispatch-workflow-form', [
                        'note' => $note,
                        'couriers' => $couriers,
                        'dispatchForm' => $dispatchForm ?? [],
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('deliver', $note)): ?>
                    <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.deliver', $note)); ?>" enctype="multipart/form-data" class="space-y-2 rounded-lg border border-erp-border p-3">
                        <?php echo csrf_field(); ?>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Proof of delivery')); ?></p>
                        <input type="text" name="recipient_name" class="erp-input text-sm" placeholder="<?php echo e(__('Recipient name')); ?>" value="<?php echo e(old('recipient_name', $note->recipient_name)); ?>" required>
                        <input type="text" name="recipient_phone" class="erp-input text-sm" placeholder="<?php echo e(__('Recipient phone')); ?>" value="<?php echo e(old('recipient_phone', $note->recipient_phone)); ?>">
                        <input type="text" name="recipient_signature" class="erp-input text-sm" placeholder="<?php echo e(__('Signature / ID reference')); ?>" value="<?php echo e(old('recipient_signature', $note->recipient_signature)); ?>">
                        <input type="file" name="pod_photo" accept="image/jpeg,image/png,image/webp" class="erp-input text-sm">
                        <textarea name="delivery_notes" rows="2" class="erp-input text-sm" placeholder="<?php echo e(__('Delivery remarks')); ?>"><?php echo e(old('delivery_notes', $note->delivery_notes)); ?></textarea>
                        <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Confirm delivery')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                    </form>
                <?php endif; ?>

                <?php if($note->status === \App\Enums\Dispatch\DeliveryNoteStatus::Delivered): ?>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                        <p class="font-semibold"><?php echo e(__('Proof of delivery captured')); ?></p>
                        <?php if($note->recipient_signature): ?>
                            <p class="mt-1"><?php echo e(__('Signature')); ?>: <?php echo e($note->recipient_signature); ?></p>
                        <?php endif; ?>
                        <?php if($note->pod_photo_path): ?>
                            <p class="mt-1"><?php echo e(__('Photo on file')); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap gap-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $note)): ?>
                        <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.cancel', $note)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="reason" value="<?php echo e(__('Cancelled by user')); ?>">
                            <?php if (isset($component)) { $__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.danger-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('danger-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Cancel')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11)): ?>
<?php $attributes = $__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11; ?>
<?php unset($__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11)): ?>
<?php $component = $__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11; ?>
<?php unset($__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11); ?>
<?php endif; ?>
                        </form>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerInvoice::class)): ?>
                        <?php if(($invoiceEligibility['eligible'] ?? false)): ?>
                            <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.generate-invoice', $note)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Generate invoice')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(! empty($dispatchReadiness['blockers'] ?? [])): ?>
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950">
                    <p class="font-semibold"><?php echo e(__('Delivery is blocked because:')); ?></p>
                    <ul class="mt-1 list-disc ps-4">
                        <?php $__currentLoopData = $dispatchReadiness['blockers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($blocker); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <?php if($note->productionJobCard): ?>
                        <p class="mt-2">
                            <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $note->productionJobCard, 'tab' => 'outputs'])); ?>" class="font-medium text-erp-primary hover:underline">
                                <?php echo e(__('Open job → Post finished goods')); ?>

                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($salesOrderInvoices->isNotEmpty() || ! empty($commercialBillingNotes)): ?>
                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                    <p class="font-semibold text-slate-900"><?php echo e(__('Commercial billing')); ?></p>
                    <?php $__currentLoopData = $commercialBillingNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billingNote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="mt-1"><?php echo e($billingNote); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($salesOrderInvoices->isNotEmpty()): ?>
                        <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-1 font-medium text-slate-600' => ! empty($commercialBillingNotes)]); ?>"><?php echo e(__('Sales order invoices')); ?></p>
                        <ul class="mt-1 space-y-1">
                            <?php $__currentLoopData = $salesOrderInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $soInvoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('admin.invoices.show', $soInvoice)); ?>" class="font-mono text-indigo-600 hover:underline"><?php echo e($soInvoice->invoice_number); ?></a>
                                    <?php if((int) $soInvoice->delivery_note_id === (int) $note->id): ?>
                                        <span class="text-slate-500">(<?php echo e(__('linked to this delivery')); ?>)</span>
                                    <?php elseif($soInvoice->delivery_note_id): ?>
                                        <span class="text-slate-500">(<?php echo e(__('other delivery')); ?>)</span>
                                    <?php else: ?>
                                        <span class="text-slate-500">(<?php echo e(__('from order')); ?>)</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if(! empty($invoiceEligibility['warnings'])): ?>
                <ul class="mt-2 list-disc ps-5 text-xs text-amber-800">
                    <?php $__currentLoopData = $invoiceEligibility['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($warning); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <?php if($showInvoiceBlockers && ! empty($invoiceEligibility['blockers'])): ?>
                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-900">
                    <p class="font-semibold"><?php echo e(__('Invoice from this delivery note')); ?></p>
                    <ul class="mt-1 list-disc ps-4">
                        <?php $__currentLoopData = $invoiceEligibility['blockers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($blocker); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
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

    <?php if(! empty($partialDelivery['is_partial'])): ?>
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
            <h3 class="mb-2 text-sm font-semibold"><?php echo e(__('Partial delivery')); ?></h3>
            <p class="mb-2 text-xs text-slate-600"><?php echo e(__('This delivery note does not cover the full sales order quantity.')); ?></p>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left"><?php echo e(__('Line')); ?></th>
                        <th class="px-3 py-2 text-right"><?php echo e(__('Ordered')); ?></th>
                        <th class="px-3 py-2 text-right"><?php echo e(__('On this DN')); ?></th>
                        <th class="px-3 py-2 text-right"><?php echo e(__('Remaining')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $partialDelivery['lines'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($row['remaining'] > 0): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">#<?php echo e($row['sales_order_item_id']); ?></td>
                                <td class="px-3 py-2 text-right font-mono"><?php echo e($row['ordered']); ?></td>
                                <td class="px-3 py-2 text-right font-mono"><?php echo e($row['delivered_on_note']); ?></td>
                                <td class="px-3 py-2 text-right font-mono"><?php echo e($row['remaining']); ?></td>
                            </tr>
                        <?php endif; ?>
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
        <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Inventory impact')); ?></h3>
        <dl class="mb-4 grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500"><?php echo e(__('FG source')); ?></dt><dd><?php echo e($inventoryImpact['finished_goods_warehouse']?->name ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Transit location')); ?></dt><dd><?php echo e($inventoryImpact['transit_warehouse']?->name ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Total inventory cost')); ?></dt><dd class="tabular-nums"><?php echo e(number_format($inventoryImpact['total_cost'] ?? 0, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Accounting posted')); ?></dt><dd><?php echo e($inventoryImpact['posted_journal'] ? __('Yes') : __('No')); ?></dd></div>
        </dl>
        <?php if($inventoryImpact['posted_journal'] ?? null): ?>
            <p class="mb-3 text-sm"><?php echo e(__('Journal')); ?>: <span class="font-mono"><?php echo e($inventoryImpact['posted_journal']->reference ?? $inventoryImpact['posted_journal']->journal_number); ?></span></p>
        <?php endif; ?>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left"><?php echo e(__('Item')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Qty')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Unit cost')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Total')); ?></th>
                    <th class="px-3 py-2 text-left"><?php echo e(__('Transit status')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__currentLoopData = $inventoryImpact['lines'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $line = $row['line']; ?>
                    <tr>
                        <td class="px-3 py-2"><?php echo e($line->inventoryItem?->sku ?? $line->description); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e($line->quantity); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format((float) ($line->unit_cost ?? 0), 4)); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e(number_format((float) ($line->total_cost ?? 0), 2)); ?></td>
                        <td class="px-3 py-2">
                            <?php if($row['delivered'] ?? false): ?>
                                <?php echo e(__('Delivered / COGS')); ?>

                            <?php elseif($row['dispatched'] ?? false): ?>
                                <?php echo e(__('In transit')); ?>

                            <?php else: ?>
                                <?php echo e(__('Pending dispatch')); ?>

                            <?php endif; ?>
                        </td>
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
        <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Line items')); ?></h3>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left"><?php echo e(__('Description')); ?></th>
                    <th class="px-3 py-2 text-right"><?php echo e(__('Qty')); ?></th>
                    <th class="px-3 py-2 text-left"><?php echo e(__('Unit')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__currentLoopData = $note->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-3 py-2"><?php echo e($item->description); ?></td>
                        <td class="px-3 py-2 text-right font-mono"><?php echo e($item->quantity); ?></td>
                        <td class="px-3 py-2"><?php echo e($item->unit); ?></td>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dispatch\delivery-notes\show.blade.php ENDPATH**/ ?>