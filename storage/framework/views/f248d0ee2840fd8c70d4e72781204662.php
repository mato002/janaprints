<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $inCommercialWorkspace = WorkspaceEmbed::inWorkspaceContext();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Counter Sales'),'breadcrumbs' => $inCommercialWorkspace
        ? [['label' => __('Counter Sales')]]
        : [
            ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
            ['label' => __('Point Of Sale'), 'url' => route('admin.workspaces.commercial.section', ['section' => 'point-of-sale'])],
            ['label' => __('Counter Sales')],
        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.commercial.pos.partials.desk-mode-nav', ['activePosView' => 'counter'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div
        id="pos-counter-root"
        x-data="posCounterWorkstation(<?php echo \Illuminate\Support\Js::from($workstationConfig)->toHtml() ?>)"
        x-init="init()"
        class="relative"
        :class="!hasSession && 'pos-counter--locked'"
    >
        <?php echo $__env->make('admin.commercial.pos.partials.workstation.session-widget', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" class="erp-btn-secondary text-sm" @click="openHeldDrawer()" x-show="permissions.canHold || permissions.canComplete">
                <?php echo e(__('Held sales')); ?>

                <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs" x-text="heldCount" x-show="heldCount > 0"></span>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12" :class="!hasSession && 'pointer-events-none opacity-60'">
            <div class="xl:col-span-3 space-y-4">
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
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Barcode scan')); ?></h3>
                    <input type="text" class="erp-input w-full font-mono" placeholder="<?php echo e(__('Scan or type barcode…')); ?>" x-ref="barcodeInput" x-model="barcodeQuery" @keydown.enter.prevent="scanBarcode()" autocomplete="off">
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
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Product search')); ?></h3>
                    <input type="search" class="erp-input w-full" placeholder="<?php echo e(__('SKU or product name…')); ?>" x-model="searchQuery" @input.debounce.300ms="searchProducts()" autocomplete="off">
                    <div class="mt-3 max-h-80 overflow-y-auto divide-y divide-erp-border" x-show="searchResults.length">
                        <template x-for="product in searchResults" :key="product.id">
                            <button type="button" class="w-full px-2 py-2 text-left text-sm hover:bg-slate-50" @click="addProduct(product)">
                                <span class="font-medium" x-text="product.name"></span>
                                <span class="block text-xs text-slate-500">
                                    <span x-show="product.sku" x-text="'SKU: ' + product.sku"></span>
                                    · <span x-text="formatMoney(product.unit_price)"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-2 text-xs text-slate-400" x-show="searchLoading"><?php echo e(__('Searching…')); ?></p>
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

            <div class="xl:col-span-6">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => '!p-0 overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 overflow-hidden']); ?>
                    <div class="border-b border-erp-border px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold"><?php echo e(__('Shopping cart')); ?></h3>
                        <span class="text-xs text-amber-700" x-show="isResume" x-text="resumeLabel"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-2"><?php echo e(__('Product')); ?></th>
                                    <th class="px-2 py-2 w-24"><?php echo e(__('Qty')); ?></th>
                                    <th class="px-2 py-2 w-28"><?php echo e(__('Unit price')); ?></th>
                                    <th class="px-2 py-2 w-24"><?php echo e(__('Discount')); ?></th>
                                    <th class="px-2 py-2 w-24"><?php echo e(__('Tax')); ?></th>
                                    <th class="px-2 py-2 w-28 text-right"><?php echo e(__('Line total')); ?></th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, index) in lines" :key="index">
                                    <tr class="border-t border-erp-border">
                                        <td class="px-4 py-2"><input type="text" class="erp-input w-full text-sm" x-model="line.description" required></td>
                                        <td class="px-2 py-2">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="decreaseQty(index)">−</button>
                                                <input type="number" step="0.001" min="0.001" class="erp-input w-16 text-center text-sm" x-model.number="line.quantity">
                                                <button type="button" class="erp-btn-secondary px-2 py-1 text-xs" @click="increaseQty(index)">+</button>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.unit_price"></td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.discount_amount"></td>
                                        <td class="px-2 py-2"><input type="number" step="0.01" min="0" class="erp-input w-full text-sm" x-model.number="line.tax_amount"></td>
                                        <td class="px-2 py-2 text-right tabular-nums font-medium" x-text="formatMoney(lineTotal(line))"></td>
                                        <td class="px-2 py-2"><button type="button" class="text-red-600 text-xs" @click="removeLine(index)">×</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-erp-border px-4 py-3 text-sm text-slate-500" x-show="!lines.length"><?php echo e(__('Scan or search products to add items to the cart.')); ?></div>
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

            <div class="xl:col-span-3 space-y-4">
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
                    <h3 class="mb-3 font-semibold"><?php echo e(__('Sale summary')); ?></h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt><?php echo e(__('Subtotal')); ?></dt><dd class="tabular-nums" x-text="formatMoney(subtotal)"></dd></div>
                        <div class="flex justify-between"><dt><?php echo e(__('Discount')); ?></dt><dd class="tabular-nums" x-text="formatMoney(totalDiscount)"></dd></div>
                        <div class="flex justify-between"><dt><?php echo e(__('Tax')); ?></dt><dd class="tabular-nums" x-text="formatMoney(totalTax)"></dd></div>
                        <div class="flex justify-between border-t border-erp-border pt-2 text-base font-bold">
                            <dt><?php echo e(__('Grand total')); ?></dt><dd class="tabular-nums" x-text="formatMoney(grandTotal)"></dd>
                        </div>
                    </dl>
                    <div class="mt-3 space-y-2">
                        <label class="text-xs text-slate-500"><?php echo e(__('Order discount')); ?></label>
                        <input type="number" step="0.01" min="0" class="erp-input w-full" x-model.number="saleDiscount">
                        <label class="text-xs text-slate-500"><?php echo e(__('Order tax')); ?></label>
                        <input type="number" step="0.01" min="0" class="erp-input w-full" x-model.number="saleTax">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold"><?php echo e(__('Customer')); ?></h3>
                        <button type="button" class="text-xs font-medium text-erp-primary" @click="showCustomerModal = true"><?php echo e(__('Change')); ?></button>
                    </div>
                    <p class="text-sm" x-text="customerLabel"></p>
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
                    <div class="flex flex-col gap-2">
                        <button type="button" class="erp-btn-secondary w-full" @click="submitHold()" :disabled="!lines.length || !permissions.canHold || isResume" x-show="permissions.canHold"><?php echo e(__('Hold sale')); ?></button>
                        <button type="button" class="erp-btn-secondary w-full text-red-700" @click="cancelSale()" x-show="permissions.canCancel"><?php echo e(__('Cancel sale')); ?></button>
                        <button type="button" class="erp-btn-primary w-full" @click="openPaymentModal()" :disabled="!lines.length || !permissions.canComplete"><?php echo e(__('Complete sale')); ?></button>
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
            </div>
        </div>

        <?php echo $__env->make('admin.commercial.pos.partials.workstation.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.commercial.pos.partials.workstation.drawers', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php echo $__env->make('admin.commercial.pos.partials.counter-sales-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/pos/counter-sales.blade.php ENDPATH**/ ?>