<?php
    $inputId = str_replace(['[', ']', '.'], '_', $name);
?>

<?php switch($type):
    case ('boolean'): ?>
        <select id="<?php echo e($inputId); ?>" name="<?php echo e($name); ?>" class="erp-select w-full min-w-[10rem]">
            <?php if($allowInherit ?? false): ?>
                <option value="inherit" <?php if($value === null): echo 'selected'; endif; ?>><?php echo e($placeholder ?? __('Inherit')); ?></option>
            <?php endif; ?>
            <option value="1" <?php if($value === true || $value === 1 || $value === '1'): echo 'selected'; endif; ?>><?php echo e(__('Yes')); ?></option>
            <option value="0" <?php if($value === false || $value === 0 || $value === '0'): echo 'selected'; endif; ?>><?php echo e(__('No')); ?></option>
        </select>
        <?php break; ?>

    <?php case ('integer'): ?>
        <input
            id="<?php echo e($inputId); ?>"
            type="number"
            name="<?php echo e($name); ?>"
            value="<?php echo e($value !== null ? $value : ''); ?>"
            placeholder="<?php echo e($placeholder ?? ''); ?>"
            class="erp-input w-full min-w-[10rem]"
        >
        <?php break; ?>

    <?php default: ?>
        <input
            id="<?php echo e($inputId); ?>"
            type="text"
            name="<?php echo e($name); ?>"
            value="<?php echo e($value !== null ? $value : ''); ?>"
            placeholder="<?php echo e($placeholder ?? ''); ?>"
            class="erp-input w-full min-w-[10rem]"
        >
<?php endswitch; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\setting-input.blade.php ENDPATH**/ ?>