<?php
    $indexUrl = $indexUrl ?? \App\Support\Production\ProductionFloorDeskViews::registerIndexUrl();
?>

<div class="job-cards-register-filters sticky top-0 z-20 -mx-1 mb-4 space-y-3 rounded-lg border border-erp-border bg-erp-page/95 px-1 py-3 backdrop-blur supports-[backdrop-filter]:bg-erp-page/80">
    <nav class="job-cards-register-filters__tabs flex flex-wrap gap-1.5" aria-label="<?php echo e(__('Production stage')); ?>">
        <?php $__currentLoopData = $statusTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($tab['url']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $tab['active'],
                ]); ?>"
                data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"
            ><?php echo e($tab['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <details class="workspace-filter-panel">
        <summary><?php echo e(__('Search & filters')); ?></summary>

    <form
        method="GET"
        action="<?php echo e($indexUrl); ?>"
        x-data="erpIndexFilterForm()"
        @change="onFieldChange($event)"
        class="space-y-3"
        data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"
    >
        <input type="hidden" name="view" value="register">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="min-w-0 flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-600" for="job-cards-search"><?php echo e(__('Quick search')); ?></label>
                <div class="relative">
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    <input
                        id="job-cards-search"
                        type="search"
                        name="search"
                        value="<?php echo e($filters['search'] ?? ''); ?>"
                        class="erp-input w-full py-2 pl-9 text-sm"
                        placeholder="<?php echo e(__('Job number, customer, order, product…')); ?>"
                        data-erp-auto-search
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600" for="saved-view"><?php echo e(__('Saved view')); ?></label>
                    <select id="saved-view" class="erp-select min-w-[10rem] text-sm" @change="applyPreset($event.target.value)">
                        <option value=""><?php echo e(__('Choose preset…')); ?></option>
                        <?php $__currentLoopData = $savedViewPresets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($preset['key']); ?>"><?php echo e($preset['label']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <template x-for="view in customViews" :key="view.id">
                            <option :value="view.id" x-text="view.label"></option>
                        </template>
                    </select>
                </div>

                <button type="button" class="erp-btn-secondary text-sm" @click="saveCurrentView()"><?php echo e(__('Save view')); ?></button>

                <div class="relative" @click.outside="columnsOpen = false">
                    <button type="button" class="erp-btn-secondary text-sm" @click="columnsOpen = !columnsOpen">
                        <?php echo e(__('Columns')); ?>

                    </button>
                    <div
                        x-show="columnsOpen"
                        x-cloak
                        class="absolute end-0 z-30 mt-1 min-w-[12rem] rounded-lg border border-erp-border bg-white py-2 shadow-lg"
                    >
                        <template x-for="column in columns" :key="column.key">
                            <label class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-sm hover:bg-slate-50">
                                <input type="checkbox" class="rounded border-slate-300" :checked="isColumnVisible(column.key)" @change="toggleColumn(column.key)">
                                <span x-text="column.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <a href="<?php echo e($indexUrl); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"><?php echo e(__('Reset')); ?></a>
            </div>
        </div>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-2.5 text-sm font-medium text-erp-primary"><?php echo e(__('Advanced filters')); ?></summary>
            <div class="grid grid-cols-1 gap-3 border-t border-erp-border px-4 py-3 md:grid-cols-2 xl:grid-cols-4">
                <?php if(filled($filters['stage'] ?? null)): ?>
                    <input type="hidden" name="stage" value="<?php echo e($filters['stage']); ?>">
                <?php endif; ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Status')); ?></label>
                    <select name="status" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('All statuses')); ?></option>
                        <?php $__currentLoopData = $filterOptions['statuses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', $status->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Priority')); ?></label>
                    <select name="priority" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('All priorities')); ?></option>
                        <?php $__currentLoopData = $filterOptions['priorities'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($priority->value); ?>" <?php if(($filters['priority'] ?? '') === $priority->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($priority->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Customer')); ?></label>
                    <select name="customer_id" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('All customers')); ?></option>
                        <?php $__currentLoopData = $filterOptions['customers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($customer->id); ?>" <?php if(($filters['customer_id'] ?? null) == $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->company_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Sales order')); ?></label>
                    <select name="sales_order_id" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('All orders')); ?></option>
                        <?php $__currentLoopData = $filterOptions['sales_orders'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($order->id); ?>" <?php if(($filters['sales_order_id'] ?? null) == $order->id): echo 'selected'; endif; ?>><?php echo e($order->order_number); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Department')); ?></label>
                    <select name="work_center_id" class="erp-input w-full text-sm">
                        <option value=""><?php echo e(__('All departments')); ?></option>
                        <?php $__currentLoopData = $filterOptions['work_centers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $center): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($center->id); ?>" <?php if(($filters['work_center_id'] ?? null) == $center->id): echo 'selected'; endif; ?>><?php echo e($center->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Date from')); ?></label>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" class="erp-input w-full text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600"><?php echo e(__('Date to')); ?></label>
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" class="erp-input w-full text-sm" />
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 border-t border-erp-border px-4 py-2 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="due_today" value="1" <?php if($filters['due_today'] ?? false): echo 'checked'; endif; ?> class="rounded border-slate-300" />
                    <?php echo e(__('Due today')); ?>

                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="overdue" value="1" <?php if($filters['overdue'] ?? false): echo 'checked'; endif; ?> class="rounded border-slate-300" />
                    <?php echo e(__('Overdue')); ?>

                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="awaiting_qc" value="1" <?php if($filters['awaiting_qc'] ?? false): echo 'checked'; endif; ?> class="rounded border-slate-300" />
                    <?php echo e(__('Awaiting QC')); ?>

                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="ready_dispatch" value="1" <?php if($filters['ready_dispatch'] ?? false): echo 'checked'; endif; ?> class="rounded border-slate-300" />
                    <?php echo e(__('Ready dispatch')); ?>

                </label>
            </div>
        </details>
    </form>

    <?php if(count($activeChips) > 0): ?>
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2">
            <span class="text-xs font-medium text-slate-500"><?php echo e(__('Active filters')); ?>:</span>
            <?php $__currentLoopData = $activeChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($chip['url']); ?>" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>">
                    <?php echo e($chip['label']); ?>

                    <span aria-hidden="true">×</span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    </details>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/register/filters.blade.php ENDPATH**/ ?>