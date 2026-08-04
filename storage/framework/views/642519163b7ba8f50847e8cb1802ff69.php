<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Employee 360').' · '.$employee->full_name,'breadcrumbs' => [['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => $employee->full_name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="employee-360"
        x-data="{
            tab: new URLSearchParams(window.location.search).get('tab') || 'overview',
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
                const more = this.$el.querySelector('.employee-360__more[open]');
                if (more) more.removeAttribute('open');
            },
        }"
    >
        <?php echo $__env->make('admin.hr.employees.360.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.hr.employees.360.kpi-strip', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="employee-360__tabs" aria-label="<?php echo e(__('Employee workspace tabs')); ?>">
            <div class="employee-360__tabs-track">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        class="employee-360__tab"
                        :class="tab === <?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?> ? 'is-active' : ''"
                        @click="setTab(<?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?>)"
                    >
                        <?php echo e($tabDef['label']); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </nav>

        <div class="employee-360__panels">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="employee-360__panel"
                    x-show="tab === <?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?>"
                    <?php if($tabDef['id'] !== 'overview'): ?> x-cloak <?php endif; ?>
                >
                    <?php echo $__env->make('admin.hr.employees.360.tabs.'.$tabDef['id'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <nav class="employee-360__mobile-bar" aria-label="<?php echo e(__('Quick actions')); ?>">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $employee)): ?>
                <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Edit')); ?></a>
            <?php endif; ?>
            <button type="button" class="erp-btn-secondary" @click="setTab('timeline')"><?php echo e(__('Timeline')); ?></button>
        </nav>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\show.blade.php ENDPATH**/ ?>