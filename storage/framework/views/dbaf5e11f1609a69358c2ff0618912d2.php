<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'options' => [],
    'param' => 'status',
    'current' => null,
    'turboFrame' => null,
    'formMode' => true,
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
    'options' => [],
    'param' => 'status',
    'current' => null,
    'turboFrame' => null,
    'formMode' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $current = $current ?? request($param, '');
    $resolvedTurboFrame = $turboFrame ?? WorkspaceEmbed::turboFrame();

    $normalizeValue = static function ($value): string {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    };

    $isAllValue = static function ($value): bool {
        return $value === '' || $value === 'all' || $value === null;
    };

    $currentValue = $normalizeValue($current);
?>

<?php if(count($options) > 1): ?>
    <div <?php echo e($attributes->merge(['class' => 'erp-table-chips flex flex-wrap gap-1.5'])); ?> role="tablist" aria-label="<?php echo e(__('Filter by :param', ['param' => str_replace('_', ' ', $param)])); ?>">
        <?php if($formMode): ?>
            <input type="hidden" name="<?php echo e($param); ?>" value="<?php echo e($currentValue); ?>">

            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $value = $option['value'] ?? $option['id'] ?? '';
                    $label = $option['label'] ?? ($isAllValue($value) ? __('All') : $value);
                    $storedValue = $normalizeValue($value);
                    $isActive = $isAllValue($value)
                        ? ($currentValue === '' || $currentValue === 'all')
                        : $currentValue === (string) $value;
                ?>
                <button
                    type="button"
                    data-erp-filter-pill
                    data-erp-filter-param="<?php echo e($param); ?>"
                    data-erp-filter-value="<?php echo e($storedValue); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'erp-filter-pill',
                        'erp-filter-pill--active' => $isActive,
                    ]); ?>"
                    role="tab"
                    <?php if($isActive): ?> aria-selected="true" <?php endif; ?>
                ><?php echo e($label); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $value = $option['value'] ?? $option['id'] ?? '';
                    $label = $option['label'] ?? ($value === '' || $value === 'all' ? __('All') : $value);
                    $query = request()->query();
                    if ($value === '' || $value === 'all') {
                        unset($query[$param]);
                    } else {
                        $query[$param] = $value;
                    }
                    unset($query['page']);
                    if (WorkspaceEmbed::inWorkspaceContext()) {
                        $query['embedded'] = '1';
                    }
                    $url = url()->current().($query !== [] ? '?'.http_build_query($query) : '');
                    $isActive = $isAllValue($value)
                        ? ($currentValue === '' || $currentValue === 'all')
                        : $currentValue === (string) $value;
                ?>
                <a
                    href="<?php echo e($url); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'erp-filter-pill',
                        'erp-filter-pill--active' => $isActive,
                    ]); ?>"
                    <?php if($resolvedTurboFrame): ?> data-turbo-frame="<?php echo e($resolvedTurboFrame); ?>" <?php endif; ?>
                    role="tab"
                    <?php if($isActive): ?> aria-selected="true" <?php endif; ?>
                ><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/status-pills.blade.php ENDPATH**/ ?>