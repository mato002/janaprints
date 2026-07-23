<?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => $indexRoute,'resetUrl' => $indexRoute,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($indexRoute),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($indexRoute),'compact' => true]); ?>
    <input type="hidden" name="view" value="queue">
    <?php if(! empty($activeDepartment ?? $active_department ?? null)): ?>
        <input type="hidden" name="department" value="<?php echo e($activeDepartment ?? $active_department); ?>">
    <?php endif; ?>
    <input id="search" type="search" name="search" value="<?php echo e($filters['search']); ?>" class="erp-toolbar-input min-w-[10rem] flex-1" placeholder="<?php echo e(__('Search jobs…')); ?>" aria-label="<?php echo e(__('Search')); ?>" data-erp-auto-search>

    <input id="from_date" type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="erp-toolbar-input hidden lg:inline-block" aria-label="<?php echo e(__('Logged from')); ?>" data-erp-auto-submit>
    <input id="to_date" type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="erp-toolbar-input hidden lg:inline-block" aria-label="<?php echo e(__('Logged to')); ?>" data-erp-auto-submit>

    <?php if(filled($filters['from_date'] ?? null) || filled($filters['to_date'] ?? null)): ?>
        <input type="hidden" name="all_dates" value="0">
        <a href="<?php echo e($indexRoute.(str_contains($indexRoute, '?') ? '&' : '?')); ?>all_dates=1" class="erp-toolbar-link text-xs whitespace-nowrap"><?php echo e(__('All logged dates')); ?></a>
    <?php endif; ?>

    <select id="due" name="due" class="erp-toolbar-select" aria-label="<?php echo e(__('Due date')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All dates')); ?></option>
        <option value="today" <?php if($filters['due'] === 'today'): echo 'selected'; endif; ?>><?php echo e(__('Today')); ?></option>
        <option value="tomorrow" <?php if($filters['due'] === 'tomorrow'): echo 'selected'; endif; ?>><?php echo e(__('Tomorrow')); ?></option>
        <option value="week" <?php if($filters['due'] === 'week'): echo 'selected'; endif; ?>><?php echo e(__('This week')); ?></option>
        <option value="month" <?php if($filters['due'] === 'month'): echo 'selected'; endif; ?>><?php echo e(__('This month')); ?></option>
        <option value="overdue" <?php if($filters['due'] === 'overdue'): echo 'selected'; endif; ?>><?php echo e(__('Overdue')); ?></option>
    </select>

    <select id="priority" name="priority" class="erp-toolbar-select" aria-label="<?php echo e(__('Priority')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All priorities')); ?></option>
        <?php $__currentLoopData = App\Enums\ProductionPriority::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($priority->value); ?>" <?php if($filters['priority'] === $priority->value): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $priority->value))); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <select id="status" name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Status')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All statuses')); ?></option>
        <?php $__currentLoopData = App\Enums\ProductionQueueStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queueStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($queueStatus->value); ?>" <?php if($filters['status'] === $queueStatus->value): echo 'selected'; endif; ?>>
                <?php echo e($workspace->statusLabel($queueStatus)); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <option value="blocked" <?php if($filters['status'] === 'blocked'): echo 'selected'; endif; ?>><?php echo e(__('Blocked (unassigned)')); ?></option>
    </select>

    <select id="work_center_id" name="work_center_id" class="erp-toolbar-select hidden md:inline-block" aria-label="<?php echo e(__('Work center')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All work centres')); ?></option>
        <?php $__currentLoopData = $workCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $center): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($center->id); ?>" <?php if((string) $filters['work_center_id'] === (string) $center->id): echo 'selected'; endif; ?>><?php echo e($center->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <select id="machine_id" name="machine_id" class="erp-toolbar-select hidden lg:inline-block" aria-label="<?php echo e(__('Machine')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All machines')); ?></option>
        <?php $__currentLoopData = $machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($machine->id); ?>" <?php if((string) $filters['machine_id'] === (string) $machine->id): echo 'selected'; endif; ?>><?php echo e($machine->asset_name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <select id="operator_id" name="operator_id" class="erp-toolbar-select hidden lg:inline-block" aria-label="<?php echo e(__('Operator')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All operators')); ?></option>
        <option value="unassigned" <?php if($filters['operator_id'] === 'unassigned'): echo 'selected'; endif; ?>><?php echo e(__('Unassigned')); ?></option>
        <?php $__currentLoopData = $operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($operator->id); ?>" <?php if((string) $filters['operator_id'] === (string) $operator->id): echo 'selected'; endif; ?>><?php echo e($operator->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <?php if($customers->isNotEmpty()): ?>
        <select id="customer_id" name="customer_id" class="erp-toolbar-select hidden xl:inline-block" aria-label="<?php echo e(__('Customer')); ?>" data-erp-auto-submit>
            <option value=""><?php echo e(__('All customers')); ?></option>
            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($customer->id); ?>" <?php if((string) $filters['customer_id'] === (string) $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->company_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/toolbar.blade.php ENDPATH**/ ?>