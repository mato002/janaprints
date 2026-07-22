<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $log->reference_number,'breadcrumbs' => [
        ['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')],
        ['label' => $log->reference_number],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="comm-log-360 mx-auto max-w-[1200px]"
        x-data="{
            tab: new URLSearchParams(window.location.search).get('tab') || 'overview',
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        <?php echo $__env->make('admin.communications.logs.360._data', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.communications.logs.360.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.communications.logs.360.kpi-strip', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="comm-log-360__tabs" aria-label="<?php echo e(__('Communication workspace tabs')); ?>">
            <?php $__currentLoopData = [
                'overview' => __('Overview'),
                'timeline' => __('Timeline'),
                'recipients' => __('Recipients'),
                'audit' => __('Audit'),
                'analytics' => __('Analytics'),
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    type="button"
                    class="comm-log-360__tab"
                    :class="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?> && 'comm-log-360__tab--active'"
                    @click="setTab(<?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>)"
                    :aria-selected="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>"
                >
                    <?php echo e($label); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="comm-log-360__panels">
            <div x-show="tab === 'overview'" x-cloak class="comm-log-360__panel">
                <?php echo $__env->make('admin.communications.logs.360.tab-overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'timeline'" x-cloak class="comm-log-360__panel">
                <?php echo $__env->make('admin.communications.logs.360.tab-timeline', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'recipients'" x-cloak class="comm-log-360__panel">
                <?php echo $__env->make('admin.communications.logs.360.tab-recipients', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'audit'" x-cloak class="comm-log-360__panel">
                <?php echo $__env->make('admin.communications.logs.360.tab-audit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'analytics'" x-cloak class="comm-log-360__panel">
                <?php echo $__env->make('admin.communications.logs.360.tab-analytics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\show.blade.php ENDPATH**/ ?>