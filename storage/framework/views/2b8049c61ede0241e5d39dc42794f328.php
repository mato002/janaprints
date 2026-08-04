<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'branch_id',
    'branches',
    'selected' => null,
    'selectClass' => 'erp-input mt-1 min-w-[10rem]',
    'labelClass' => 'text-[11px] text-slate-500',
    'showLabel' => true,
]));

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

foreach (array_filter(([
    'name' => 'branch_id',
    'branches',
    'selected' => null,
    'selectClass' => 'erp-input mt-1 min-w-[10rem]',
    'labelClass' => 'text-[11px] text-slate-500',
    'showLabel' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $canViewConsolidated = app(\App\Support\Security\ConsolidatedViewGovernance::class)->canViewConsolidated(auth()->user());
?>

<div>
    <?php if($showLabel): ?>
        <label class="<?php echo e($labelClass); ?>" for="<?php echo e($name); ?>"><?php echo e(__('Branch')); ?></label>
    <?php endif; ?>
    <select id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" <?php echo e($attributes->merge(['class' => $selectClass])); ?>>
        <?php if($canViewConsolidated): ?>
            <option value="" <?php if($selected === null || $selected === ''): echo 'selected'; endif; ?>><?php echo e(__('All branches')); ?></option>
        <?php endif; ?>
        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch->id); ?>" <?php if((string) ($selected ?? '') === (string) $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\consolidated-branch-select.blade.php ENDPATH**/ ?>