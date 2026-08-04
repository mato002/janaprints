<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'categories',
    'filters' => [],
    'categoryName' => 'category_id',
    'subcategoryName' => 'subcategory_id',
    'categoryLabel' => null,
    'subcategoryLabel' => null,
    'selectClass' => 'erp-toolbar-select',
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
    'categories',
    'filters' => [],
    'categoryName' => 'category_id',
    'subcategoryName' => 'subcategory_id',
    'categoryLabel' => null,
    'subcategoryLabel' => null,
    'selectClass' => 'erp-toolbar-select',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $categoryLabel ??= __('Category');
    $subcategoryLabel ??= __('Subcategory');
    $selectedCategory = (string) ($filters[$categoryName] ?? '');
    $selectedSubcategory = (string) ($filters[$subcategoryName] ?? '');
?>

<div
    x-data="erpCategorySubcategoryFilter({
        refreshUrl: <?php echo \Illuminate\Support\Js::from(route('admin.lookups.subcategories'))->toHtml() ?>,
        categoryId: <?php echo \Illuminate\Support\Js::from($selectedCategory)->toHtml() ?>,
        subcategoryId: <?php echo \Illuminate\Support\Js::from($selectedSubcategory)->toHtml() ?>,
    })"
    <?php echo e($attributes->class(['contents'])); ?>

>
    <select
        id="<?php echo e($categoryName); ?>"
        name="<?php echo e($categoryName); ?>"
        class="<?php echo e($selectClass); ?>"
        aria-label="<?php echo e($categoryLabel); ?>"
        x-model="categoryId"
        @change="onCategoryChange()"
    >
        <option value=""><?php echo e(__('All categories')); ?></option>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select
        id="<?php echo e($subcategoryName); ?>"
        name="<?php echo e($subcategoryName); ?>"
        class="<?php echo e($selectClass); ?>"
        aria-label="<?php echo e($subcategoryLabel); ?>"
        x-model="subcategoryId"
        :disabled="! categoryId"
    >
        <option value=""><?php echo e(__('All subcategories')); ?></option>
        <template x-for="option in subcategories" :key="option.value">
            <option :value="String(option.value)" x-text="option.label"></option>
        </template>
    </select>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\category-subcategory-filters.blade.php ENDPATH**/ ?>