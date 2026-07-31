<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['filters', 'branches', 'customers', 'salespersons', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['filters', 'branches', 'customers', 'salespersons', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\QuotationStatus;
    $toolbarAction = $filter_action ?? route('admin.commercial.reports.quotations.index');
    $toolbarResetUrl = $filter_reset_url ?? route('admin.commercial.reports.quotations.index');
?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => $toolbarAction,'resetUrl' => $toolbarResetUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($toolbarAction),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($toolbarResetUrl)]); ?>
        <?php if($report_options): ?>
            <?php echo $__env->make('admin.commercial.reports.partials.report-type-select', [
                'report_options' => $report_options,
                'report_key' => $report_key,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
        <input type="hidden" name="tab" value="<?php echo e($filters['tab'] ?? 'summary'); ?>">
        <input type="date" id="from_date" name="from_date" value="<?php echo e($filters['from_date']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From date')); ?>">
        <input type="date" id="to_date" name="to_date" value="<?php echo e($filters['to_date']); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To date')); ?>">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>">
            <option value=""><?php echo e(__('All branches')); ?></option>
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php if(($filters['branch_id'] ?? null) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="customer_id" name="customer_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Customer')); ?>">
            <option value=""><?php echo e(__('All customers')); ?></option>
            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($customer->id); ?>" <?php if(($filters['customer_id'] ?? null) == $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->company_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="salesperson_id" name="salesperson_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Salesperson')); ?>">
            <option value=""><?php echo e(__('All salespersons')); ?></option>
            <?php $__currentLoopData = $salespersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($salesperson->id); ?>" <?php if(($filters['salesperson_id'] ?? null) == $salesperson->id): echo 'selected'; endif; ?>><?php echo e($salesperson->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="expiry_status" name="expiry_status" class="erp-toolbar-select" aria-label="<?php echo e(__('Expiry status')); ?>">
            <option value=""><?php echo e(__('All')); ?></option>
            <option value="valid" <?php if(($filters['expiry_status'] ?? '') === 'valid'): echo 'selected'; endif; ?>><?php echo e(__('Valid')); ?></option>
            <option value="expiring_soon" <?php if(($filters['expiry_status'] ?? '') === 'expiring_soon'): echo 'selected'; endif; ?>><?php echo e(__('Expiring Soon')); ?></option>
            <option value="expired" <?php if(($filters['expiry_status'] ?? '') === 'expired'): echo 'selected'; endif; ?>><?php echo e(__('Expired')); ?></option>
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="<?php echo e($filters['search'] ?? ''); ?>"
            placeholder="<?php echo e(__('Quote number or customer name…')); ?>"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="<?php echo e(__('Search')); ?>"
        >
        <?php if (isset($component)) { $__componentOriginal8d71058e635815f8f51e2bf876db5ad4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pills','data' => ['options' => collect(QuotationStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all(),'param' => 'status','current' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pills'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(QuotationStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all()),'param' => 'status','current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $attributes = $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__attributesOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4)): ?>
<?php $component = $__componentOriginal8d71058e635815f8f51e2bf876db5ad4; ?>
<?php unset($__componentOriginal8d71058e635815f8f51e2bf876db5ad4); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/reports/quotations/partials/filters.blade.php ENDPATH**/ ?>