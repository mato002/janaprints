<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['filters', 'branches', 'customers', 'designers', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null]));

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

foreach (array_filter((['filters', 'branches', 'customers', 'designers', 'report_options' => null, 'report_key' => null, 'filter_action' => null, 'filter_reset_url' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Enums\ArtworkApprovalDecision;
    use App\Enums\ArtworkRequestStatus;
    $toolbarAction = $filter_action ?? route('admin.commercial.reports.artwork.index');
    $toolbarResetUrl = $filter_reset_url ?? route('admin.commercial.reports.artwork.index');
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
        <?php if($report_key): ?>
            <input type="hidden" name="report" value="<?php echo e($report_key); ?>">
        <?php endif; ?>
        <input type="hidden" name="tab" value="<?php echo e($filters['tab'] ?? 'requests'); ?>">
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
        <select id="designer_id" name="designer_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Designer')); ?>">
            <option value=""><?php echo e(__('All designers')); ?></option>
            <?php $__currentLoopData = $designers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($designer->id); ?>" <?php if(($filters['designer_id'] ?? null) == $designer->id): echo 'selected'; endif; ?>><?php echo e($designer->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="approval_status" name="approval_status" class="erp-toolbar-select" aria-label="<?php echo e(__('Approval status')); ?>">
            <option value=""><?php echo e(__('All approval outcomes')); ?></option>
            <?php $__currentLoopData = ArtworkApprovalDecision::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $decision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($decision->value); ?>" <?php if(($filters['approval_status'] ?? '') === $decision->value): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $decision->value))); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select id="delay_status" name="delay_status" class="erp-toolbar-select" aria-label="<?php echo e(__('Delay status')); ?>">
            <option value=""><?php echo e(__('All delay states')); ?></option>
            <option value="delayed" <?php if(($filters['delay_status'] ?? '') === 'delayed'): echo 'selected'; endif; ?>><?php echo e(__('Delayed')); ?></option>
            <option value="on_time" <?php if(($filters['delay_status'] ?? '') === 'on_time'): echo 'selected'; endif; ?>><?php echo e(__('On time')); ?></option>
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="<?php echo e($filters['search'] ?? ''); ?>"
            placeholder="<?php echo e(__('Request number or title…')); ?>"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="<?php echo e(__('Search')); ?>"
        >
        <?php if (isset($component)) { $__componentOriginal8d71058e635815f8f51e2bf876db5ad4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d71058e635815f8f51e2bf876db5ad4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-pills','data' => ['options' => collect(ArtworkRequestStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all(),'param' => 'status','current' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-pills'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(ArtworkRequestStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst(str_replace('_', ' ', $status->value))])->prepend(['value' => '', 'label' => __('All statuses')])->all()),'param' => 'status','current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\artwork\partials\filters.blade.php ENDPATH**/ ?>