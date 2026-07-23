<?php
    $operatorMode = (bool) ($operatorMode ?? false);
    $action = $action ?? [];
    $jobKey = $jobKey ?? null;
    $buttonClass = $buttonClass ?? 'erp-btn-primary text-xs py-1 px-2';
?>

<?php if(($action['type'] ?? '') === 'post'): ?>
    <form method="POST" action="<?php echo e($action['url']); ?>" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
        <?php echo csrf_field(); ?>
        <?php if($operatorMode): ?>
            <input type="hidden" name="from" value="production-floor">
        <?php endif; ?>
        <button type="submit" class="<?php echo e($buttonClass); ?>"><?php echo e($action['label']); ?></button>
    </form>
<?php elseif(in_array($action['type'] ?? '', ['modal', 'qc'], true)): ?>
    <?php
        $target = $action['target'] ?? (($action['type'] ?? '') === 'qc' ? 'qc' : '');
    ?>
    <button
        type="button"
        class="<?php echo e($buttonClass); ?>"
        @click="openActionModal(<?php echo \Illuminate\Support\Js::from($jobKey)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($target)->toHtml() ?>)"
    ><?php echo e($action['label']); ?></button>
<?php elseif(($action['type'] ?? '') === 'panel'): ?>
    <?php
        $panelFragment = parse_url($action['url'], PHP_URL_FRAGMENT) ?: '';
        $panelTarget = match ($panelFragment) {
            'quality' => 'qc',
            'outsource' => str_contains(strtolower($action['label']), 'return') ? 'outsource-return' : 'outsource-send',
            'fulfilment' => 'fulfilment',
            default => null,
        };
        $panelLinkUrl = $action['url'];
        if ($operatorMode) {
            $panelLinkUrl .= str_contains($panelLinkUrl, '?') ? '&' : '?';
            $panelLinkUrl .= 'from=production-floor';
        }
    ?>
    <?php if($panelTarget): ?>
        <button type="button" class="<?php echo e($buttonClass); ?>" @click="openActionModal(<?php echo \Illuminate\Support\Js::from($jobKey)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($panelTarget)->toHtml() ?>)"><?php echo e($action['label']); ?></button>
    <?php else: ?>
        <a
            href="<?php echo e($panelLinkUrl); ?>"
            class="<?php echo e($buttonClass); ?>"
            <?php if($operatorMode): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
            @click.stop
        ><?php echo e($action['label']); ?></a>
    <?php endif; ?>
<?php else: ?>
    <?php
        $linkUrl = $action['url'];
        if ($operatorMode) {
            $linkUrl .= str_contains($linkUrl, '?') ? '&' : '?';
            $linkUrl .= 'from=production-floor';
        }
    ?>
    <a href="<?php echo e($linkUrl); ?>" class="<?php echo e(str_replace('erp-btn-primary', 'erp-btn-secondary', $buttonClass)); ?>" <?php if($operatorMode): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>><?php echo e($action['label']); ?></a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\floor\partials\next-step-action.blade.php ENDPATH**/ ?>