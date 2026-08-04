<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Payroll Run 360').' · '.$run->reference,'breadcrumbs' => [['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => __('Runs'), 'url' => route('admin.hr.payroll.index')], ['label' => $run->reference]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="payroll-run-360"
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
        <?php echo $__env->make('admin.hr.payroll.360.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.hr.payroll.360.kpi-strip', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="<?php echo e(__('Payroll run workspace tabs')); ?>">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="tab === <?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?> ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="setTab(<?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?>)"
                >
                    <?php echo e($tabDef['label']); ?>

                    <?php if($tabDef['id'] === 'review' && ! ($review['can_submit_for_approval'] ?? true)): ?>
                        <span class="ml-1 inline-flex h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

<?php if($errors->any()): ?>
            <div hidden data-erp-validation-errors>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e($error); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="payroll-run-360__panels">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div x-show="tab === <?php echo \Illuminate\Support\Js::from($tabDef['id'])->toHtml() ?>" <?php if($tabDef['id'] !== 'overview'): ?> x-cloak <?php endif; ?>>
                    <?php echo $__env->make('admin.hr.payroll.360.tabs.'.$tabDef['id'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\show.blade.php ENDPATH**/ ?>