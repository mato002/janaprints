<?php
    $presentation = $presentation ?? null;
    $message = $message ?? ($presentation['message'] ?? null);
    $detail = $detail ?? ($presentation['detail'] ?? null);
?>

<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
    <p class="text-sm font-medium"><?php echo e($message ?? __('Something went wrong while processing this form.')); ?></p>
    <?php if($detail): ?>
        <p class="mt-2 text-xs text-rose-700"><?php echo e($detail); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\partials\governed-form-errors.blade.php ENDPATH**/ ?>