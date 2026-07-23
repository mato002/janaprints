<?php
    $fulfilment = $tabData['fulfilment'] ?? null;
    $method = $tabData['fulfilment_method'] ?? null;
    $ready = $tabData['ready_for_dispatch'] ?? false;
    $canFulfil = $tabData['can_fulfil'] ?? false;
?>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
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
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Fulfilment method')); ?></h3>
        <p class="text-lg font-medium"><?php echo e($method?->label() ?? __('Collection')); ?></p>
        <p class="mt-2 text-sm text-slate-600"><?php echo e(__('From sales order')); ?></p>
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
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Status')); ?></h3>
        <p class="text-lg font-medium"><?php echo e($fulfilment?->status?->label() ?? __('Pending')); ?></p>
        <?php if($tabData['invoice_ready'] ?? false): ?>
            <span class="erp-badge mt-2 bg-emerald-100 text-emerald-800"><?php echo e(__('Ready for invoice')); ?></span>
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
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Job readiness')); ?></h3>
        <p class="text-sm <?php echo e($ready ? 'text-emerald-700' : 'text-amber-700'); ?>">
            <?php echo e($ready ? __('Ready for dispatch') : __('Job not yet ready for fulfilment')); ?>

        </p>
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

<?php if($method?->value === 'collection'): ?>
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
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Collection')); ?></h3>

        <?php if($fulfilment?->prepared_at): ?>
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500"><?php echo e(__('Prepared by')); ?></dt><dd><?php echo e($fulfilment->preparedByUser?->name ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Prepared at')); ?></dt><dd><?php echo e($fulfilment->prepared_at->format('M j, Y H:i')); ?></dd></div>
                <?php if($fulfilment->collection_notes): ?>
                    <div class="md:col-span-2"><dt class="text-slate-500"><?php echo e(__('Collection notes')); ?></dt><dd><?php echo e($fulfilment->collection_notes); ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if($fulfilment?->collected_at): ?>
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2 border-t border-erp-border pt-4">
                <div><dt class="text-slate-500"><?php echo e(__('Collected by')); ?></dt><dd><?php echo e($fulfilment->collected_by_name); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Collected at')); ?></dt><dd><?php echo e($fulfilment->collected_at->format('M j, Y H:i')); ?></dd></div>
                <?php if($fulfilment->collector_id_number): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('ID number')); ?></dt><dd><?php echo e($fulfilment->collector_id_number); ?></dd></div>
                <?php endif; ?>
                <?php if($fulfilment->collector_phone): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Phone')); ?></dt><dd><?php echo e($fulfilment->collector_phone); ?></dd></div>
                <?php endif; ?>
                <?php if($fulfilment->collection_remarks): ?>
                    <div class="md:col-span-2"><dt class="text-slate-500"><?php echo e(__('Remarks')); ?></dt><dd><?php echo e($fulfilment->collection_remarks); ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if($canFulfil && $ready): ?>
            <?php if($fulfilment?->status?->value === 'pending'): ?>
                <form method="POST" action="<?php echo e(route('admin.production.job-cards.fulfilment.ready-for-collection', $jobCard)); ?>" class="space-y-3 max-w-lg">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label"><?php echo e(__('Collection notes')); ?></label>
                        <textarea name="collection_notes" class="erp-input w-full" rows="2"></textarea>
                    </div>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Mark ready for collection')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php elseif($fulfilment?->status?->value === 'ready_for_collection'): ?>
                <form method="POST" action="<?php echo e(route('admin.production.job-cards.fulfilment.confirm-collection', [$jobCard, $fulfilment])); ?>" class="space-y-3 max-w-lg">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label"><?php echo e(__('Collected by')); ?></label>
                        <input type="text" name="collected_by_name" class="erp-input w-full" required>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="erp-label"><?php echo e(__('ID number')); ?></label>
                            <input type="text" name="collector_id_number" class="erp-input w-full">
                        </div>
                        <div>
                            <label class="erp-label"><?php echo e(__('Phone')); ?></label>
                            <input type="text" name="collector_phone" class="erp-input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Remarks')); ?></label>
                        <textarea name="collection_remarks" class="erp-input w-full" rows="2"></textarea>
                    </div>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Confirm collection')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php else: ?>
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
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Delivery')); ?></h3>

        <?php if($fulfilment?->dispatched_at || $fulfilment?->recipient_name): ?>
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500"><?php echo e(__('Recipient')); ?></dt><dd><?php echo e($fulfilment->recipient_name ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Phone')); ?></dt><dd><?php echo e($fulfilment->recipient_phone ?? '—'); ?></dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500"><?php echo e(__('Address')); ?></dt><dd><?php echo e($fulfilment->delivery_address ?? '—'); ?></dd></div>
                <?php if($fulfilment->dispatched_at): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Dispatched by')); ?></dt><dd><?php echo e($fulfilment->dispatchedByUser?->name ?? '—'); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Dispatch date')); ?></dt><dd><?php echo e($fulfilment->dispatch_date?->format('M j, Y') ?? $fulfilment->dispatched_at->format('M j, Y')); ?></dd></div>
                <?php endif; ?>
                <?php if($fulfilment->deliveryNote): ?>
                    <div class="md:col-span-2">
                        <dt class="text-slate-500"><?php echo e(__('Delivery note')); ?></dt>
                        <dd><a href="<?php echo e(route('admin.dispatch.delivery-notes.show', $fulfilment->deliveryNote)); ?>" class="font-mono text-indigo-600"><?php echo e($fulfilment->deliveryNote->delivery_note_number); ?></a></dd>
                    </div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if($fulfilment?->delivered_at): ?>
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2 border-t border-erp-border pt-4">
                <div><dt class="text-slate-500"><?php echo e(__('Received by')); ?></dt><dd><?php echo e($fulfilment->received_by ?? '—'); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Delivered at')); ?></dt><dd><?php echo e($fulfilment->delivered_at->format('M j, Y H:i')); ?></dd></div>
                <?php if($fulfilment->signature_name): ?>
                    <div><dt class="text-slate-500"><?php echo e(__('Signature name')); ?></dt><dd><?php echo e($fulfilment->signature_name); ?></dd></div>
                <?php endif; ?>
                <?php if($fulfilment->delivery_remarks): ?>
                    <div class="md:col-span-2"><dt class="text-slate-500"><?php echo e(__('Remarks')); ?></dt><dd><?php echo e($fulfilment->delivery_remarks); ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <?php if($canFulfil && $ready && ! in_array($fulfilment?->status?->value, ['delivered', 'collected'], true)): ?>
            <?php if(in_array($fulfilment?->status?->value, ['pending', null], true)): ?>
                <?php if($fulfilment && $fulfilment->recipient_name): ?>
                    <form method="POST" action="<?php echo e(route('admin.production.job-cards.fulfilment.prepare-delivery', [$jobCard, $fulfilment])); ?>" class="space-y-3 max-w-lg mb-4">
                        <?php echo csrf_field(); ?>
                        <p class="text-xs text-slate-500"><?php echo e(__('Update saved delivery details before dispatch.')); ?></p>
                        <div>
                            <label class="erp-label"><?php echo e(__('Recipient name')); ?></label>
                            <input type="text" name="recipient_name" class="erp-input w-full" value="<?php echo e(old('recipient_name', $fulfilment->recipient_name)); ?>" required>
                        </div>
                        <div>
                            <label class="erp-label"><?php echo e(__('Recipient phone')); ?></label>
                            <input type="text" name="recipient_phone" class="erp-input w-full" value="<?php echo e(old('recipient_phone', $fulfilment->recipient_phone)); ?>">
                        </div>
                        <div>
                            <label class="erp-label"><?php echo e(__('Delivery address')); ?></label>
                            <textarea name="delivery_address" class="erp-input w-full" rows="3" required><?php echo e(old('delivery_address', $fulfilment->delivery_address)); ?></textarea>
                        </div>
                        <div>
                            <label class="erp-label"><?php echo e(__('Dispatch date')); ?></label>
                            <input type="date" name="dispatch_date" class="erp-input w-full" value="<?php echo e(old('dispatch_date', $fulfilment->dispatch_date?->format('Y-m-d') ?? now()->toDateString())); ?>">
                        </div>
                        <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Save delivery details')); ?> <?php echo $__env->renderComponent(); ?>
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
                <form method="POST" action="<?php echo e(route('admin.production.job-cards.fulfilment.create-delivery', $jobCard)); ?>" class="space-y-3 max-w-lg">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label"><?php echo e(__('Recipient name')); ?></label>
                        <input type="text" name="recipient_name" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Recipient phone')); ?></label>
                        <input type="text" name="recipient_phone" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Delivery address')); ?></label>
                        <textarea name="delivery_address" class="erp-input w-full" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Dispatch date')); ?></label>
                        <input type="date" name="dispatch_date" class="erp-input w-full" value="<?php echo e(now()->toDateString()); ?>">
                    </div>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Create delivery & dispatch')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php elseif($fulfilment?->status?->value === 'dispatched'): ?>
                <form method="POST" action="<?php echo e(route('admin.production.job-cards.fulfilment.confirm-delivery', [$jobCard, $fulfilment])); ?>" class="space-y-3 max-w-lg">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label"><?php echo e(__('Received by')); ?></label>
                        <input type="text" name="received_by" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Signature name')); ?> <span class="text-slate-400">(<?php echo e(__('optional')); ?>)</span></label>
                        <input type="text" name="signature_name" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label"><?php echo e(__('Remarks')); ?></label>
                        <textarea name="delivery_remarks" class="erp-input w-full" rows="2"></textarea>
                    </div>
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
<?php endif; ?>

<p class="text-sm text-slate-500">
    <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch'])); ?>" class="text-indigo-600"><?php echo e(__('View dispatch readiness & delivery notes')); ?></a>
</p>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\fulfilment.blade.php ENDPATH**/ ?>