
<?php
    $activeTab = request()->string('tab')->toString() ?: 'overview';
    $allowedTabs = ['overview', 'commercial', 'production', 'financial', 'notes', 'attachments'];
    if (! in_array($activeTab, $allowedTabs, true)) {
        $activeTab = 'overview';
    }

    $statusLabel = str_replace('_', ' ', $salesOrder->status->value);
    $financialLabel = $financial['financial_status_label'] ?? null;
    $financialVariant = $financial['financial_status_variant'] ?? 'slate';
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $salesOrder->order_number,'breadcrumbs' => [
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="so-360"
        x-data="{
            tab: <?php echo \Illuminate\Support\Js::from($activeTab)->toHtml() ?>,
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        <?php echo $__env->make('admin.sales.orders.workspace.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.sales.orders.workspace.lifecycle-rail', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="so-360__tabs" aria-label="<?php echo e(__('Sales order workspace tabs')); ?>">
            <?php $__currentLoopData = [
                'overview' => __('Overview'),
                'commercial' => __('Commercial'),
                'production' => __('Production'),
                'financial' => __('Financial'),
                'notes' => __('Notes'),
                'attachments' => __('Attachments'),
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    type="button"
                    class="so-360__tab"
                    :class="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?> && 'so-360__tab--active'"
                    @click="setTab(<?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>)"
                    :aria-selected="tab === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>"
                ><?php echo e($label); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="so-360__panels">
            <div x-show="tab === 'overview'" class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'commercial'" x-cloak class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.commercial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'production'" x-cloak class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.production', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'financial'" x-cloak class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.financial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'notes'" x-cloak class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.notes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div x-show="tab === 'attachments'" x-cloak class="so-360__panel">
                <?php echo $__env->make('admin.sales.orders.workspace.tabs.attachments', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <?php echo $__env->make('admin.sales.orders.workspace.mobile-action-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/show.blade.php ENDPATH**/ ?>