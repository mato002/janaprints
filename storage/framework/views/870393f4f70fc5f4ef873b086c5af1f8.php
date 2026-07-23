<?php
    $action = $action ?? null;
    $completion = $completion ?? ['eligible' => false];
    $size = $size ?? 'md';
    $btnClass = $size === 'lg' ? 'job-360-hero__action erp-btn-primary' : 'erp-btn-primary text-sm';
?>

<?php if($action): ?>
    <?php if(($action['type'] ?? '') === 'post'): ?>
        <form method="POST" action="<?php echo e($action['url']); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="<?php echo \Illuminate\Support\Arr::toCssClasses([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary']); ?>">
                <?php echo e($action['label']); ?>

            </button>
        </form>
    <?php elseif(
        ($action['type'] ?? '') === 'link'
        && str_contains((string) ($action['url'] ?? ''), 'tab=outputs')
        && ($completion['eligible'] ?? false)
        && auth()->user()?->can('production.outputs.post')
    ): ?>
        <button type="button" class="<?php echo \Illuminate\Support\Arr::toCssClasses([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary']); ?>" data-open-dialog="complete-fg-modal">
            <?php echo e($action['label']); ?>

        </button>
    <?php elseif(($action['type'] ?? '') === 'link'): ?>
        <a
            href="<?php echo e($action['url']); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([$btnClass, 'erp-btn-secondary' => ($action['variant'] ?? '') !== 'primary']); ?>"
            data-turbo-frame="erp-main"
        ><?php echo e($action['label']); ?></a>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/primary-action-button.blade.php ENDPATH**/ ?>