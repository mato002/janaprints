<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $rfq->rfq_number] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Vendor Comparison — :rfq', ['rfq' => $rfq->rfq_number]),'description' => str($rfq->status->value)->headline()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Vendor Comparison — :rfq', ['rfq' => $rfq->rfq_number])),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(str($rfq->status->value)->headline())]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('compare', $rfq)): ?>
                <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.compare', $rfq)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <?php $__currentLoopData = $weights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <button type="submit" class="erp-btn-secondary"><?php echo e(__('Save comparison')); ?></button>
                </form>
            <?php endif; ?>
            <?php if($rfq->purchaseOrder): ?>
                <a href="<?php echo e(route('admin.procurement.orders.show', $rfq->purchaseOrder)); ?>" class="erp-btn-primary"><?php echo e(__('View PO')); ?></a>
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
        <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('RFQ requirements')); ?></h3>
        <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
            <div><span class="text-slate-500"><?php echo e(__('RFQ')); ?>:</span> <?php echo e($workspace['rfq']['rfq_number']); ?></div>
            <div><span class="text-slate-500"><?php echo e(__('Required date')); ?>:</span> <?php echo e($workspace['rfq']['required_date']); ?></div>
            <div><span class="text-slate-500"><?php echo e(__('Purchase request')); ?>:</span> <?php echo e($workspace['rfq']['purchase_request_number'] ?? '—'); ?></div>
        </div>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Requested item')); ?></th>
                    <th><?php echo e(__('Required quantity')); ?></th>
                    <th><?php echo e(__('Required date')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $workspace['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($item['inventory_item'] ?? $item['description']); ?></td>
                        <td><?php echo e(number_format($item['quantity'], 2)); ?></td>
                        <td><?php echo e($item['required_date']); ?></td>
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manageComparison', $rfq)): ?>
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
            <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Scoring weights')); ?></h3>
            <form method="GET" action="<?php echo e(route('admin.procurement.vendor-comparison.show', $rfq)); ?>" class="grid gap-3 sm:grid-cols-5">
                <?php $__currentLoopData = ['price' => __('Price'), 'performance' => __('Performance'), 'lead_time' => __('Lead time'), 'quality' => __('Quality')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <label class="text-[11px] text-slate-500" for="<?php echo e($key); ?>"><?php echo e($label); ?> %</label>
                        <input type="number" id="<?php echo e($key); ?>" name="<?php echo e($key); ?>" value="<?php echo e($weights[$key] ?? 0); ?>" min="0" max="100" class="erp-input mt-1 w-full">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-end">
                    <button type="submit" class="erp-btn-secondary w-full"><?php echo e(__('Recalculate')); ?></button>
                </div>
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
        <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Supplier comparison grid')); ?></h3>
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Supplier')); ?></th>
                        <th><?php echo e(__('Quoted price')); ?></th>
                        <th><?php echo e(__('Total cost')); ?></th>
                        <th><?php echo e(__('Lead time')); ?></th>
                        <th><?php echo e(__('Payment terms')); ?></th>
                        <th><?php echo e(__('Delivery terms')); ?></th>
                        <th><?php echo e(__('Warranty')); ?></th>
                        <th><?php echo e(__('Supplier rating')); ?></th>
                        <th><?php echo e(__('Historical performance')); ?></th>
                        <th><?php echo e(__('Score')); ?></th>
                        <th><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $workspace['matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $highlights = $workspace['highlights'];
                            $classes = [];
                            if (($highlights['lowest_price_vendor_id'] ?? null) === $row['vendor_id']) {
                                $classes[] = 'bg-emerald-50';
                            }
                            if (($highlights['best_lead_time_vendor_id'] ?? null) === $row['vendor_id']) {
                                $classes[] = 'ring-1 ring-sky-200';
                            }
                            if (($highlights['best_score_vendor_id'] ?? null) === $row['vendor_id']) {
                                $classes[] = 'font-semibold';
                            }
                        ?>
                        <tr class="<?php echo e(implode(' ', $classes)); ?>">
                            <td>
                                <?php echo e($row['vendor_name']); ?>

                                <div class="text-[11px] text-slate-500"><?php echo e(str($row['invitation_status'])->headline()); ?></div>
                            </td>
                            <td><?php echo e(number_format($row['quoted_price'], 2)); ?></td>
                            <td><?php echo e(number_format($row['total_cost'], 2)); ?></td>
                            <td><?php echo e($row['avg_lead_time_days'] ?? '—'); ?> <?php echo e(__('days')); ?></td>
                            <td><?php echo e($row['payment_terms']); ?></td>
                            <td><?php echo e($row['delivery_terms']); ?></td>
                            <td><?php echo e($row['warranty']); ?></td>
                            <td><?php echo e($row['supplier_rating'] ?? '—'); ?></td>
                            <td><?php echo e(isset($row['historical_performance']) ? $row['historical_performance'].'%' : '—'); ?></td>
                            <td><?php echo e($row['score']); ?></td>
                            <td class="space-y-1">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('award', $rfq)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.award', $rfq)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="vendor_id" value="<?php echo e($row['vendor_id']); ?>">
                                        <input type="hidden" name="auto_po" value="1">
                                        <button class="erp-link"><?php echo e(__('Award')); ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manageComparison', $rfq)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.reject', [$rfq, $row['rfq_vendor_id']])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="erp-link text-red-600"><?php echo e(__('Reject quote')); ?></button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.requote', [$rfq, $row['rfq_vendor_id']])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="erp-link"><?php echo e(__('Request requote')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('award', $rfq)): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
            <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Award partial quantity')); ?></h3>
            <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.award-partial', $rfq)); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="auto_po" value="1">
                <div>
                    <label class="text-[11px] text-slate-500" for="partial_vendor_id"><?php echo e(__('Supplier')); ?></label>
                    <select id="partial_vendor_id" name="vendor_id" class="erp-input mt-1 w-full max-w-md" required>
                        <?php $__currentLoopData = $workspace['matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($row['vendor_id']); ?>"><?php echo e($row['vendor_name']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php $__currentLoopData = $workspace['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="sm:col-span-2 text-sm"><?php echo e($item['description']); ?> (<?php echo e(__('max')); ?> <?php echo e(number_format($item['quantity'], 2)); ?>)</div>
                        <input type="hidden" name="lines[<?php echo e($index); ?>][rfq_item_id]" value="<?php echo e($item['id']); ?>">
                        <input type="number" step="0.001" name="lines[<?php echo e($index); ?>][quantity]" class="erp-input" placeholder="<?php echo e(__('Award qty')); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Award partial quantity')); ?> <?php echo $__env->renderComponent(); ?>
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

        <?php if(count($workspace['items']) > 0 && count($workspace['matrix']) > 1): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4']); ?>
                <h3 class="mb-3 text-sm font-semibold"><?php echo e(__('Split award')); ?></h3>
                <form method="POST" action="<?php echo e(route('admin.procurement.vendor-comparison.split-award', $rfq)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="auto_po" value="1">
                    <?php $__currentLoopData = $workspace['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemIndex => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-lg border border-erp-border p-3">
                            <p class="mb-2 text-sm font-medium"><?php echo e($item['description']); ?> — <?php echo e(number_format($item['quantity'], 2)); ?></p>
                            <?php $__currentLoopData = $workspace['matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendorIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="mb-2 grid gap-2 sm:grid-cols-3">
                                    <div class="text-sm"><?php echo e($row['vendor_name']); ?></div>
                                    <input type="hidden" name="allocations[<?php echo e($itemIndex); ?>_<?php echo e($vendorIndex); ?>][vendor_id]" value="<?php echo e($row['vendor_id']); ?>">
                                    <input type="hidden" name="allocations[<?php echo e($itemIndex); ?>_<?php echo e($vendorIndex); ?>][rfq_item_id]" value="<?php echo e($item['id']); ?>">
                                    <input type="number" step="0.001" name="allocations[<?php echo e($itemIndex); ?>_<?php echo e($vendorIndex); ?>][quantity]" class="erp-input" placeholder="<?php echo e(__('Qty')); ?>">
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Split award')); ?> <?php echo $__env->renderComponent(); ?>
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
    <?php endif; ?>

    <?php if($rfq->awardedVendor): ?>
        <p class="mt-4 text-sm text-slate-600">
            <?php echo e(__('Awarded supplier')); ?>: <strong><?php echo e($rfq->awardedVendor->vendor_name); ?></strong>
            <?php if($rfq->award_type): ?>
                · <?php echo e(str($rfq->award_type)->headline()); ?>

            <?php endif; ?>
        </p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\vendor-comparison\show.blade.php ENDPATH**/ ?>