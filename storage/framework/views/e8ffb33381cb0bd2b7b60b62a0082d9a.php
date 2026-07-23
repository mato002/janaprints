<?php
    use App\Enums\InventoryStockRole;

    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $eligible = (bool) ($completion['eligible'] ?? false);
    $blockers = $completion['blockers'] ?? [];
    $remaining = count($blockers);
    $suggestedId = old('finished_inventory_item_id', $completion['suggested_finished_item_id'] ?? null);
    $suggestedQty = old('quantity_completed', $completion['suggested_quantity_completed'] ?? 1);
    $suggestedItem = ($finishedItems ?? collect())->firstWhere('id', $suggestedId) ?? $jobCard->inventoryItem;
    $qtyLabel = number_format((float) $suggestedQty, 0);
    $postLabel = __('Post :qty finished goods', ['qty' => $qtyLabel]);
    $warehouseLabel = ($completion['fg_warehouse']['code'] ?? null)
        ? ($completion['fg_warehouse']['code'].' — '.$completion['fg_warehouse']['name'])
        : __('Finished goods virtual warehouse');
    $needsRole = $suggestedItem && ($suggestedItem->stock_role ?? null) !== InventoryStockRole::FinishedGood;
?>

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
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Finished goods output')); ?></h3>
            <p class="text-sm text-slate-600"><?php echo e(__('Post completed production into finished goods inventory when all requirements are met.')); ?></p>
        </div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
            <div class="flex flex-wrap items-center gap-2">
                <?php if($eligible): ?>
                    <button type="button" class="erp-btn-primary text-sm" data-open-dialog="complete-fg-modal"><?php echo e($postLabel); ?></button>
                <?php else: ?>
                    <button type="button" class="erp-btn-primary text-sm opacity-60" disabled><?php echo e(__('Post to finished goods')); ?></button>
                    <?php if($remaining > 0): ?>
                        <span class="text-xs font-medium text-amber-700"><?php echo e(trans_choice(':count requirement remaining|:count requirements remaining', $remaining, ['count' => $remaining])); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Finished item')); ?></dt>
            <dd class="mt-1 text-slate-800">
                <?php if($suggestedItem): ?>
                    <span class="font-mono text-xs text-slate-500"><?php echo e($suggestedItem->sku); ?></span><br>
                    <?php echo e($suggestedItem->item_name); ?>

                    <?php if($needsRole): ?>
                        <span class="mt-1 block text-xs text-amber-700"><?php echo e(__('Set stock role to Finished Good')); ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo e(__('Not resolved yet')); ?>

                <?php endif; ?>
            </dd>
        </div>
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Completed qty')); ?></dt>
            <dd class="mt-1 tabular-nums text-slate-800"><?php echo e($qtyLabel); ?></dd>
        </div>
        <div class="rounded-md border border-erp-border bg-slate-50 px-3 py-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Warehouse')); ?></dt>
            <dd class="mt-1 text-slate-800"><?php echo e($warehouseLabel); ?></dd>
        </div>
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

<?php if(auth()->user()?->can('production.outputs.view')): ?>
    <div class="mt-4">
        <a href="<?php echo e(route('admin.production.outputs.index')); ?>" class="erp-link text-sm"><?php echo e(__('All outputs')); ?></a>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/finished-goods-post-form.blade.php ENDPATH**/ ?>