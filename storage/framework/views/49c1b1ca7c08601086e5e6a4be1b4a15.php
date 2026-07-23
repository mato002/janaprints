<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'label',
    'submittingLabel' => null,
    'submittingMessage' => null,
    'buttonClass' => 'erp-btn-secondary',
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
    'action',
    'label',
    'submittingLabel' => null,
    'submittingMessage' => null,
    'buttonClass' => 'erp-btn-secondary',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $submittingLabel ??= __('Sending email…');
    $submittingMessage ??= $submittingLabel;
?>

<form
    method="POST"
    action="<?php echo e($action); ?>"
    <?php echo e($attributes->merge(['class' => 'inline'])); ?>

    data-erp-submit-feedback
    data-erp-submitting-message="<?php echo e($submittingMessage); ?>"
>
    <?php echo csrf_field(); ?>
    <button type="submit" class="<?php echo e($buttonClass); ?>" data-erp-submit-feedback-button aria-busy="false">
        <span data-erp-submit-feedback-label class="inline-flex items-center gap-2"><?php echo e($label); ?></span>
        <span data-erp-submit-feedback-loading class="hidden items-center gap-2">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <?php echo e($submittingLabel); ?>

        </span>
    </button>
</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/documents/email-submit-form.blade.php ENDPATH**/ ?>