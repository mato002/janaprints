<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Scheduling'),'breadcrumbs' => [
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Scheduling')],
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('Scheduling'),'description' => __('Plan production jobs by date and work center using job card planned dates.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Scheduling')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Plan production jobs by date and work center using job card planned dates.'))]); ?>
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

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-6">
        <?php $__currentLoopData = [
            ['label' => __('Scheduled Jobs'), 'value' => $kpis['scheduled'], 'icon' => 'calendar'],
            ['label' => __('Unscheduled Jobs'), 'value' => $kpis['unscheduled'], 'icon' => 'clock'],
            ['label' => __('Overdue Jobs'), 'value' => $kpis['overdue'], 'icon' => 'exclamation'],
            ['label' => __('Upcoming Jobs'), 'value' => $kpis['upcoming'], 'icon' => 'sparkles'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $kpi['label'],'value' => (string) $kpi['value'],'icon' => $kpi['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((string) $kpi['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['icon'])]); ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-erp-border bg-white p-0.5 text-sm">
            <a
                href="<?php echo e(route('admin.production.scheduling.index', array_merge(request()->except('page'), ['view' => 'list']))); ?>"
                class="rounded-md px-3 py-1.5 <?php echo e($viewMode === 'list' ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"
            >
                <?php echo e(__('List view')); ?>

            </a>
            <a
                href="<?php echo e(route('admin.production.scheduling.index', array_merge(request()->except('page'), ['view' => 'calendar']))); ?>"
                class="rounded-md px-3 py-1.5 <?php echo e($viewMode === 'calendar' ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"
            >
                <?php echo e(__('Calendar view')); ?>

            </a>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
        <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.production.scheduling.index'),'resetUrl' => route('admin.production.scheduling.index', ['view' => $viewMode]),'compact' => true,'class' => 'erp-index-toolbar-form--compact']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.production.scheduling.index')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.production.scheduling.index', ['view' => $viewMode])),'compact' => true,'class' => 'erp-index-toolbar-form--compact']); ?>
            <?php if($viewMode === 'list'): ?>
                 <?php $__env->slot('export', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['exportRoute' => 'admin.production.scheduling.export','exportQuery' => request()->query()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-route' => 'admin.production.scheduling.export','export-query' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->query())]); ?>
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
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>

            <input type="hidden" name="view" value="<?php echo e($viewMode); ?>">
            <?php if($viewMode === 'calendar'): ?>
                <input type="hidden" name="month" value="<?php echo e($filters['month']); ?>">
            <?php endif; ?>
            <input id="search" type="search" name="search" value="<?php echo e($filters['search']); ?>" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="<?php echo e(__('Job number or customer…')); ?>" aria-label="<?php echo e(__('Search')); ?>" data-erp-auto-search>
            <select id="status" name="status" class="erp-toolbar-select" aria-label="<?php echo e(__('Job Status')); ?>">
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if($filters['status'] === $status->value): echo 'selected'; endif; ?>>
                        <?php echo e(str_replace('_', ' ', ucfirst($status->value))); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select id="priority" name="priority" class="erp-toolbar-select" aria-label="<?php echo e(__('Priority')); ?>">
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($priority->value); ?>" <?php if($filters['priority'] === $priority->value): echo 'selected'; endif; ?>>
                        <?php echo e(str_replace('_', ' ', ucfirst($priority->value))); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select id="work_center_id" name="work_center_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Work Center')); ?>">
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $workCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $center): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($center->id); ?>" <?php if((string) $filters['work_center_id'] === (string) $center->id): echo 'selected'; endif; ?>><?php echo e($center->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input id="date_from" type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From')); ?>">
            <input id="date_to" type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To')); ?>">
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

    <?php if($viewMode === 'calendar' && $calendar): ?>
        <?php echo $__env->make('admin.production.scheduling.partials.calendar', ['calendar' => $calendar, 'filters' => $filters], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <section aria-label="<?php echo e(__('Schedule register')); ?>">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Schedule Register')); ?></h2>
            <?php echo $__env->make('admin.production.scheduling.partials.list', ['jobs' => $jobs, 'workspace' => $workspace], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </section>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/scheduling/index.blade.php ENDPATH**/ ?>