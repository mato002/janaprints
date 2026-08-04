<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $exportQuery = array_merge(
        collect($filters)->filter(fn ($value) => filled($value))->all(),
        ($activeDepartment ?? null) ? ['department' => $activeDepartment] : []
    );

    // Keep date defaults collapsed — only open when advanced criteria are active.
    $moreOpen = filled($filters['due'] ?? null)
        || filled($filters['work_center_id'] ?? null)
        || filled($filters['machine_id'] ?? null)
        || filled($filters['operator_id'] ?? null)
        || filled($filters['customer_id'] ?? null);

    $resolvedAction = WorkspaceEmbed::url($indexRoute) ?? $indexRoute;
    $resolvedResetUrl = WorkspaceEmbed::url($indexRoute) ?? $indexRoute;
    $frame = WorkspaceEmbed::turboFrame();
    $embedded = WorkspaceEmbed::inWorkspaceContext();
?>

<form
    method="GET"
    action="<?php echo e($resolvedAction); ?>"
    class="erp-index-toolbar-form erp-index-toolbar-form--compact erp-index-toolbar-form--dense"
    <?php if($frame): ?> data-turbo-frame="<?php echo e($frame); ?>" <?php endif; ?>
    x-data="{ moreFilters: <?php echo e($moreOpen ? 'true' : 'false'); ?> }"
>
    <?php if($embedded): ?>
        <input type="hidden" name="embedded" value="1">
    <?php endif; ?>

    <div class="erp-index-toolbar border-b border-erp-border bg-white px-2 py-1.5 sm:px-3">
        <div class="erp-index-toolbar-row flex items-center gap-1.5">
            <div class="flex min-w-0 flex-1 flex-nowrap items-center gap-1.5 overflow-x-auto">
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="<?php echo e($filters['search']); ?>"
                    class="erp-toolbar-input min-w-[12rem] flex-[1.6]"
                    placeholder="<?php echo e(__('Search jobs…')); ?>"
                    aria-label="<?php echo e(__('Search')); ?>"
                    data-erp-auto-search
                >

                <select id="status" name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>" data-erp-auto-submit>
                    <option value=""><?php echo e(__('All statuses')); ?></option>
                    <?php $__currentLoopData = App\Enums\ProductionQueueStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queueStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($queueStatus->value); ?>" <?php if($filters['status'] === $queueStatus->value): echo 'selected'; endif; ?>>
                            <?php echo e($workspace->statusLabel($queueStatus)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <option value="blocked" <?php if($filters['status'] === 'blocked'): echo 'selected'; endif; ?>><?php echo e(__('Blocked (unassigned)')); ?></option>
                </select>

                <select id="priority" name="priority" class="erp-toolbar-select" aria-label="<?php echo e(__('Priority')); ?>" data-erp-auto-submit>
                    <option value=""><?php echo e(__('All priorities')); ?></option>
                    <?php $__currentLoopData = App\Enums\ProductionPriority::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($priority->value); ?>" <?php if($filters['priority'] === $priority->value): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $priority->value))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <button
                    type="button"
                    class="erp-btn-ghost shrink-0 whitespace-nowrap py-1 text-xs text-slate-600"
                    @click="moreFilters = ! moreFilters"
                    :aria-expanded="moreFilters.toString()"
                >
                    <span x-text="moreFilters ? <?php echo \Illuminate\Support\Js::from(__('Less filters'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('More filters'))->toHtml() ?>"></span>
                    <span aria-hidden="true" class="ml-0.5" x-text="moreFilters ? '▲' : '▼'"></span>
                </button>

                <button
                    type="button"
                    data-erp-filter-reset
                    class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                ><?php echo e(__('Reset')); ?></button>
            </div>

            <div class="ml-auto flex shrink-0 items-center gap-1.5">
                <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => 'admin.production.queue.export','exportQuery' => $exportQuery]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => 'admin.production.queue.export','export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exportQuery)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
            </div>
        </div>

        <div
            class="mt-1.5 flex w-full flex-wrap items-center gap-1.5 border-t border-erp-border/60 pt-1.5"
            x-show="moreFilters"
            x-cloak
        >
            <input id="from_date" type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Logged from')); ?>" data-erp-auto-submit>
            <input id="to_date" type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('Logged to')); ?>" data-erp-auto-submit>

            <?php if(filled($filters['from_date'] ?? null) || filled($filters['to_date'] ?? null)): ?>
                <input type="hidden" name="all_dates" value="0">
                <a
                    href="<?php echo e($resolvedResetUrl); ?>?all_dates=1"
                    class="erp-toolbar-link text-xs whitespace-nowrap"
                    <?php $__currentLoopData = WorkspaceEmbed::turboLinkAttributes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ><?php echo e(__('All logged dates')); ?></a>
            <?php endif; ?>

            <select id="due" name="due" class="erp-toolbar-select" aria-label="<?php echo e(__('Due date')); ?>" data-erp-auto-submit>
                <option value=""><?php echo e(__('All dates')); ?></option>
                <option value="today" <?php if($filters['due'] === 'today'): echo 'selected'; endif; ?>><?php echo e(__('Today')); ?></option>
                <option value="tomorrow" <?php if($filters['due'] === 'tomorrow'): echo 'selected'; endif; ?>><?php echo e(__('Tomorrow')); ?></option>
                <option value="week" <?php if($filters['due'] === 'week'): echo 'selected'; endif; ?>><?php echo e(__('This week')); ?></option>
                <option value="month" <?php if($filters['due'] === 'month'): echo 'selected'; endif; ?>><?php echo e(__('This month')); ?></option>
                <option value="overdue" <?php if($filters['due'] === 'overdue'): echo 'selected'; endif; ?>><?php echo e(__('Overdue')); ?></option>
            </select>

            <select id="work_center_id" name="work_center_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Work center')); ?>" data-erp-auto-submit>
                <option value=""><?php echo e(__('All work centres')); ?></option>
                <?php $__currentLoopData = $workCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $center): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($center->id); ?>" <?php if((string) $filters['work_center_id'] === (string) $center->id): echo 'selected'; endif; ?>><?php echo e($center->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select id="machine_id" name="machine_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Machine')); ?>" data-erp-auto-submit>
                <option value=""><?php echo e(__('All machines')); ?></option>
                <?php $__currentLoopData = $machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($machine->id); ?>" <?php if((string) $filters['machine_id'] === (string) $machine->id): echo 'selected'; endif; ?>><?php echo e($machine->asset_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select id="operator_id" name="operator_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Operator')); ?>" data-erp-auto-submit>
                <option value=""><?php echo e(__('All operators')); ?></option>
                <option value="unassigned" <?php if($filters['operator_id'] === 'unassigned'): echo 'selected'; endif; ?>><?php echo e(__('Unassigned')); ?></option>
                <?php $__currentLoopData = $operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($operator->id); ?>" <?php if((string) $filters['operator_id'] === (string) $operator->id): echo 'selected'; endif; ?>><?php echo e($operator->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <?php if($customers->isNotEmpty()): ?>
                <select id="customer_id" name="customer_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Customer')); ?>" data-erp-auto-submit>
                    <option value=""><?php echo e(__('All customers')); ?></option>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php if((string) $filters['customer_id'] === (string) $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->company_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\toolbar.blade.php ENDPATH**/ ?>