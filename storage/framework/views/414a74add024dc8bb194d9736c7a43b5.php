<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'method' => null,
    'action' => null,
    'variant' => 'default',
    'confirm' => null,
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
    'href' => null,
    'method' => null,
    'action' => null,
    'variant' => 'default',
    'confirm' => null,
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
    use App\Support\Platform\ModalFormRoutes;

    $classes = match ($variant) {
        'danger' => 'border-t border-red-100 text-red-700 hover:bg-red-50',
        default => 'text-erp-primary hover:bg-erp-page',
    };
?>

<?php if($action && $method): ?>
    <form method="POST" action="<?php echo e($action); ?>" class="block" <?php if($confirm): ?> onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($confirm)->toHtml() ?>)" <?php endif; ?>>
        <?php echo csrf_field(); ?>
        <?php if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'])): ?>
            <?php echo method_field($method); ?>
        <?php endif; ?>
        <button type="submit" @click="$dispatch('erp-row-menu-close')" <?php echo e($attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"])); ?>>
            <?php echo e($slot); ?>

        </button>
    </form>
<?php elseif($href): ?>
    <?php
        $isModalOpen = $attributes->has('data-erp-modal-open')
            || (! $attributes->has('data-no-modal') && ModalFormRoutes::supportsUrl($href));
        $resolvedHref = $href;
        $linkAttributes = $attributes->merge([
            'class' => "flex w-full items-center gap-2 px-3 py-2 text-sm {$classes}",
        ]);

        $isDownloadLink = is_string($href) && (str_contains($href, '/download') || str_ends_with($href, '.pdf'));

        if ($isModalOpen) {
            $resolvedHref = WorkspaceEmbed::mainUrl($href) ?? $href;
            $linkAttributes = $linkAttributes->merge(['data-erp-modal-open' => true]);
        } elseif ($attributes->get('data-turbo') !== 'false') {
            if ($isDownloadLink) {
                $linkAttributes = $linkAttributes->merge(['data-turbo' => 'false']);
            } else {
                // Default: entity hops (View / Edit) load into erp-main and leave the
                // nested workspace frame. Callers may override data-turbo-frame.
                $frameAttributes = [
                    'data-turbo-frame' => 'erp-main',
                    'data-turbo-action' => 'advance',
                ];
                $resolvedHref = WorkspaceEmbed::mainUrl($href) ?? $href;

                if ($attributes->has('data-turbo-frame')) {
                    unset($frameAttributes['data-turbo-frame']);
                    $resolvedHref = WorkspaceEmbed::url($href) ?? $href;
                }

                $linkAttributes = $linkAttributes->merge($frameAttributes);
            }
        }
    ?>
    <a
        href="<?php echo e($resolvedHref); ?>"
        @click="$dispatch('erp-row-menu-close')"
        <?php echo e($linkAttributes); ?>

    >
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button type="button" <?php echo e($attributes->merge(['class' => "flex w-full items-center gap-2 px-3 py-2 text-left text-sm {$classes}"])); ?>>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\table-row-action.blade.php ENDPATH**/ ?>