<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Customer 360 Workspace').' · '.$customer->company_name,'breadcrumbs' => [['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => $customer->company_name]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="crm-360"
        x-data="{
            tab: (() => {
                const requested = new URLSearchParams(window.location.search).get('tab') || 'overview';
                return requested === 'artwork' ? 'print-specifications' : requested;
            })(),
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        <?php echo $__env->make('admin.crm.customers.360._data', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.crm.customers.360.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.crm.customers.360.kpi-strip', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="crm-360__tabs" aria-label="<?php echo e(__('Customer workspace tabs')); ?>">
            <?php $__currentLoopData = [
                'overview' => __('Overview'),
                'conversations' => __('Conversations'),
                'communications' => __('Communications'),
                'commercial' => __('Commercial'),
                'files' => __('Files'),
                'activities' => __('Activities'),
                'notes' => __('Notes'),
                'timeline' => __('Timeline'),
                'print-specifications' => __('Print Specifications'),
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    type="button"
                    class="crm-360__tab"
                    :class="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?> && 'crm-360__tab--active'"
                    @click="setTab(<?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>)"
                    :aria-selected="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>"
                >
                    <?php echo e($label); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="crm-360__panels">
            <div x-show="tab === 'overview'" class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'conversations'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-conversations', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'communications'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-communications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'commercial'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-commercial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'files'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-files', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'activities'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-activities', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'notes'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-notes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'timeline'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-timeline', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'print-specifications'" x-cloak class="crm-360__panel">
                <?php echo $__env->make('admin.crm.customers.360.tab-print-specifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\show.blade.php ENDPATH**/ ?>