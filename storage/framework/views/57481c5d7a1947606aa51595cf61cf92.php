<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url',
    'filename' => null,
    'label' => null,
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
    'url',
    'filename' => null,
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $label ??= __('Download PDF');
    $filename ??= 'document';
?>

<button
    type="button"
    data-document-pdf-download
    data-document-pdf-download-url="<?php echo e($url); ?>"
    data-document-pdf-download-filename="<?php echo e($filename); ?>"
    <?php echo e($attributes->merge(['class' => 'erp-btn-secondary'])); ?>

    aria-busy="false"
>
    <span data-document-pdf-download-label class="inline-flex items-center gap-2">
        <?php echo e($label); ?>

    </span>
    <span data-document-pdf-download-loading class="hidden items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <?php echo e(__('Downloading…')); ?>

    </span>
</button>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\documents\pdf-download-button.blade.php ENDPATH**/ ?>