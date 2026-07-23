<?php if(($action['type'] ?? null) === 'generate'): ?>
    <form
        method="POST"
        action="<?php echo e(route($action['route'], $run)); ?>"
        class="inline"
        <?php if($action['needs_confirm'] ?? false): ?>
            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Regenerating will replace all existing payroll lines. Continue?'))->toHtml() ?>)"
        <?php endif; ?>
    >
        <?php echo csrf_field(); ?>
        <?php if($action['needs_confirm'] ?? false): ?>
            <input type="hidden" name="confirm_regenerate" value="1">
        <?php endif; ?>
        <button type="submit" class="erp-btn-primary"><?php echo e($action['label']); ?></button>
    </form>
<?php elseif(($action['type'] ?? null) === 'post'): ?>
    <form
        method="POST"
        action="<?php echo e(route($action['route'], $run)); ?>"
        class="inline"
        data-turbo-frame="erp-main"
        <?php if($action['needs_confirm'] ?? false): ?>
            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($action['confirm_message'] ?? __('Continue with this action?'))->toHtml() ?>)"
        <?php endif; ?>
    >
        <?php echo csrf_field(); ?>
        <button
            type="submit"
            class="<?php echo e(($action['variant'] ?? null) === 'danger' ? 'erp-btn-secondary text-red-700 border-red-200' : 'erp-btn-secondary'); ?>"
            <?php if(($action['variant'] ?? null) === 'danger' && ! ($action['needs_confirm'] ?? false)): ?>
                onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('Cancel this payroll run?'))->toHtml() ?>)"
            <?php endif; ?>
        ><?php echo e($action['label']); ?></button>
    </form>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\360\partials\quick-action.blade.php ENDPATH**/ ?>