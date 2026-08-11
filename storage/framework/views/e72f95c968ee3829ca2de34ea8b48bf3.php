<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'file',
    'accept' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'inputClass' => 'artwork-detail-file-upload__input',
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
    'name' => 'file',
    'accept' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'inputClass' => 'artwork-detail-file-upload__input',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uploadLabel = $label ?? __('Choose file');
    $uploadHint = $hint ?? __('PDF, AI, PSD, PNG, JPG…');
?>

<div
    x-data="{ fileName: '' }"
    <?php echo e($attributes->merge(['class' => 'artwork-detail-file-upload'])); ?>

    :class="{ 'artwork-detail-file-upload--has-file': fileName !== '' }"
    @click="$refs.fileInput.click()"
    @keydown.enter.prevent="$refs.fileInput.click()"
    @keydown.space.prevent="$refs.fileInput.click()"
    role="button"
    tabindex="0"
>
    <input
        x-ref="fileInput"
        type="file"
        name="<?php echo e($name); ?>"
        <?php if($accept): ?> accept="<?php echo e($accept); ?>" <?php endif; ?>
        <?php if($required): ?> required <?php endif; ?>
        class="<?php echo e($inputClass); ?>"
        @change="fileName = $event.target.files?.[0]?.name ?? ''"
    >
    <svg class="artwork-detail-file-upload__icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
    </svg>
    <p class="artwork-detail-file-upload__label" x-show="!fileName"><?php echo e($uploadLabel); ?></p>
    <p class="artwork-detail-file-upload__hint" x-show="!fileName"><?php echo e($uploadHint); ?></p>
    <p class="artwork-detail-file-upload__name" x-show="fileName" x-text="fileName"></p>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\file-upload.blade.php ENDPATH**/ ?>