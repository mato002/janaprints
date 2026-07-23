<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $priceBook->name,'breadcrumbs' => [
        ['label' => __('Commercial')],
        ['label' => __('Sales')],
        ['label' => __('Price Books'), 'url' => route('admin.commercial.price-books.index')],
        ['label' => $priceBook->name],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="price-book-show w-full min-w-0 space-y-3">
        <div class="price-book-show__toolbar">
            <a
                href="<?php echo e(route('admin.commercial.price-books.index')); ?>"
                class="price-book-show__back"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/>
                </svg>
                <?php echo e(__('Back to Price Books')); ?>

            </a>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                <a href="<?php echo e(route('admin.commercial.price-books.edit', $priceBook)); ?>" class="erp-btn-secondary erp-btn--sm"><?php echo e(__('Edit')); ?></a>
            <?php endif; ?>
        </div>

        <section class="price-book-show__hero">
            <div class="price-book-show__hero-main">
                <div class="price-book-show__hero-top">
                    <h1 class="price-book-show__title"><?php echo e($priceBook->name); ?></h1>
                    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $priceBook->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priceBook->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral')]); ?>
                        <?php echo e($priceBook->status->label()); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                    <?php if($priceBook->is_default): ?>
                        <span class="price-book-show__chip"><?php echo e(__('Default')); ?></span>
                    <?php endif; ?>
                </div>
                <?php if($priceBook->description): ?>
                    <p class="price-book-show__description"><?php echo e($priceBook->description); ?></p>
                <?php endif; ?>
            </div>

            <dl class="price-book-show__meta">
                <div class="price-book-show__meta-item">
                    <dt><?php echo e(__('Code')); ?></dt>
                    <dd class="font-mono"><?php echo e($priceBook->code); ?></dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt><?php echo e(__('Branch')); ?></dt>
                    <dd><?php echo e($priceBook->branch?->name ?? __('Company-wide')); ?></dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt><?php echo e(__('Items')); ?></dt>
                    <dd><?php echo e($priceBook->items->count()); ?></dd>
                </div>
                <div class="price-book-show__meta-item">
                    <dt><?php echo e(__('Customers')); ?></dt>
                    <dd><?php echo e($priceBook->customerAssignments->count()); ?></dd>
                </div>
            </dl>
        </section>

        <div class="price-book-show__layout">
            <section class="price-book-show__panel">
                <header class="price-book-show__panel-head">
                    <h2 class="price-book-show__panel-title"><?php echo e(__('Price book items')); ?></h2>
                    <span class="price-book-show__panel-count"><?php echo e($priceBook->items->count()); ?></span>
                </header>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                    <form
                        method="POST"
                        action="<?php echo e(route('admin.commercial.price-books.items.store', $priceBook)); ?>"
                        class="price-book-show__inline-form"
                    >
                        <?php echo csrf_field(); ?>
                        <select name="inventory_item_id" class="erp-input price-book-show__input" required>
                            <option value=""><?php echo e(__('Inventory item')); ?></option>
                            <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>"><?php echo e($item->item_name); ?> (<?php echo e($item->sku); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input type="number" step="0.01" name="unit_price" class="erp-input price-book-show__input price-book-show__input--price" placeholder="<?php echo e(__('Unit price')); ?>" required>
                        <input type="number" step="0.0001" name="minimum_quantity" class="erp-input price-book-show__input price-book-show__input--qty" placeholder="<?php echo e(__('Min qty')); ?>">
                        <button type="submit" class="erp-btn-primary erp-btn--sm shrink-0"><?php echo e(__('Add item')); ?></button>
                    </form>
                <?php endif; ?>

                <div class="price-book-show__table-wrap">
                    <table class="price-book-show__table erp-table w-full text-sm">
                        <colgroup>
                            <col class="price-book-show__col-item">
                            <col class="price-book-show__col-price">
                            <col class="price-book-show__col-qty">
                            <col class="price-book-show__col-status">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                                <col class="price-book-show__col-actions">
                            <?php endif; ?>
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col"><?php echo e(__('Item')); ?></th>
                                <th scope="col" class="price-book-show__col--numeric"><?php echo e(__('Unit price')); ?></th>
                                <th scope="col" class="price-book-show__col--numeric"><?php echo e(__('Min qty')); ?></th>
                                <th scope="col"><?php echo e(__('Status')); ?></th>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                                    <th scope="col" class="erp-table-actions-col"></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $priceBook->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="font-medium"><?php echo e($item->inventoryItem?->item_name ?? $item->service_code ?? $item->description); ?></td>
                                    <td class="price-book-show__col--numeric tabular-nums"><?php echo e(number_format((float) $item->unit_price, 2)); ?></td>
                                    <td class="price-book-show__col--numeric tabular-nums"><?php echo e($item->minimum_quantity ?? '—'); ?></td>
                                    <td>
                                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $item->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->status === App\Enums\CommercialPriceBookStatus::Active ? 'success' : 'neutral')]); ?>
                                            <?php echo e($item->status->label()); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                                    </td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                                        <td class="erp-table-actions-col">
                                            <form method="POST" action="<?php echo e(route('admin.commercial.price-books.items.destroy', [$priceBook, $item])); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="price-book-show__remove"><?php echo e(__('Remove')); ?></button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(auth()->user()?->can('update', $priceBook) ? 5 : 4); ?>" class="price-book-show__empty">
                                        <?php echo e(__('No items yet.')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="price-book-show__aside">
                <section class="price-book-show__panel">
                    <header class="price-book-show__panel-head">
                        <h2 class="price-book-show__panel-title"><?php echo e(__('Customer assignments')); ?></h2>
                        <span class="price-book-show__panel-count"><?php echo e($priceBook->customerAssignments->count()); ?></span>
                    </header>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $priceBook)): ?>
                        <form
                            method="POST"
                            action="<?php echo e(route('admin.commercial.price-books.assign-customer', $priceBook)); ?>"
                            class="price-book-show__assign-form"
                        >
                            <?php echo csrf_field(); ?>
                            <select name="customer_id" class="erp-input price-book-show__input min-w-0 flex-1" required>
                                <option value=""><?php echo e(__('Select customer')); ?></option>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->company_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="submit" class="erp-btn-secondary erp-btn--sm shrink-0" <?php if($customers->isEmpty()): echo 'disabled'; endif; ?>><?php echo e(__('Assign')); ?></button>
                        </form>
                    <?php endif; ?>

                    <ul class="price-book-show__assignments" role="list">
                        <?php $__empty_1 = true; $__currentLoopData = $priceBook->customerAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="price-book-show__assignment">
                                <div class="price-book-show__assignment-copy">
                                    <span class="price-book-show__assignment-name"><?php echo e($assignment->customer?->company_name); ?></span>
                                    <span class="price-book-show__assignment-status"><?php echo e($assignment->status->label()); ?></span>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="price-book-show__empty"><?php echo e(__('No customer assignments.')); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>
            </aside>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\price-books\show.blade.php ENDPATH**/ ?>