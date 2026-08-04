<?php
    $field = str_replace('.', '_', $key);
    $resolved = $resolver->get($key);
    $displayValue = old($field);

    if ($displayValue === null) {
        if ($meta['type'] === 'json') {
            $displayValue = json_encode($resolved ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($meta['type'] === 'boolean') {
            $displayValue = (bool) $resolved;
        } else {
            $displayValue = $resolved;

            if (
                ($meta['optional'] ?? false)
                && ($meta['type'] ?? '') === 'url'
                && (! is_string($displayValue) || $displayValue === '#' || ! filter_var($displayValue, FILTER_VALIDATE_URL))
            ) {
                $displayValue = '';
            }
        }
    }
?>

<?php if($key === 'footer.social'): ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', App\Models\WebsiteSetting::class)): ?>
        <details class="lg:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-4" data-settings-field data-settings-label="advanced social json" data-settings-key="footer.social">
            <summary class="cursor-pointer text-sm font-medium text-slate-700"><?php echo e(__('Advanced Settings (JSON)')); ?></summary>
            <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Use the Social Links tab for Facebook, Instagram, LinkedIn, and Twitter/X. Only edit this JSON if you need advanced control.')); ?></p>
            <textarea id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" rows="6" class="erp-input mt-3 font-mono text-xs"><?php echo e($displayValue); ?></textarea>
            <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </details>
    <?php endif; ?>
<?php elseif($meta['type'] === 'json' && in_array($key, ['footer.nav', 'footer.trust_badges'], true)): ?>
    <?php echo $__env->make('admin.website.settings.json-rows', [
        'key' => $key,
        'meta' => $meta,
        'record' => $record,
        'resolved' => $resolved,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['lg:col-span-2' => in_array($meta['type'], ['json'], true), 'settings-field' => true]); ?>" data-settings-field data-settings-label="<?php echo e(strtolower($meta['label'])); ?>" data-settings-key="<?php echo e(strtolower($key)); ?>">
    <label class="erp-label" for="<?php echo e($field); ?>"><?php echo e($meta['label']); ?></label>
    <?php if(! empty($meta['description'])): ?>
        <p class="mb-1 text-xs text-slate-500"><?php echo e($meta['description']); ?></p>
    <?php endif; ?>

    <?php if(($record?->value || $record?->fallback_value) && $resolved !== null && (string) $record?->value !== (string) $record?->fallback_value): ?>
        <p class="mb-2 text-xs text-slate-500">
            <?php echo e(__('Fallback:')); ?>

            <span class="font-mono"><?php echo e(is_array($record?->fallback_value ? json_decode((string) $record->fallback_value, true) : null) ? __('JSON structure from config') : Str::limit((string) ($record?->fallback_value ?? $resolved), 80)); ?></span>
        </p>
    <?php endif; ?>

    <?php if($meta['type'] === 'json'): ?>
        <textarea id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" rows="6" class="erp-input font-mono text-xs" required><?php echo e($displayValue); ?></textarea>
        <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Must be valid JSON. Example for social links:')); ?> <code>[{"label":"Instagram","href":"https://instagram.com/brand","icon":"instagram"}]</code></p>
    <?php elseif($meta['type'] === 'boolean'): ?>
        <input type="hidden" name="<?php echo e($field); ?>" value="0">
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
            <input
                id="<?php echo e($field); ?>"
                name="<?php echo e($field); ?>"
                type="checkbox"
                value="1"
                class="rounded border-slate-300 text-brand-magenta focus:ring-brand-magenta"
                <?php if((bool) $displayValue): echo 'checked'; endif; ?>
            >
            <span><?php echo e(__('Enabled')); ?></span>
        </label>
    <?php else: ?>
        <input
            id="<?php echo e($field); ?>"
            name="<?php echo e($field); ?>"
            type="<?php echo e(in_array($meta['type'], ['email', 'url']) ? $meta['type'] : 'text'); ?>"
            class="erp-input"
            value="<?php echo e($meta['type'] === 'json' ? '' : $displayValue); ?>"
            <?php if(! ($meta['optional'] ?? false)): ?> required <?php endif; ?>
        >
    <?php endif; ?>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <p class="font-mono text-xs text-slate-400"><?php echo e($key); ?></p>
        <?php if($record?->fallback_value): ?>
            <span class="text-xs text-slate-500"><?php echo e(__('Config fallback preserved in database.')); ?></span>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', App\Models\WebsiteSetting::class)): ?>
            <?php if($record?->value): ?>
                <button
                    type="button"
                    class="text-xs text-slate-500 underline hover:text-slate-700"
                    data-website-settings-reset-trigger
                    data-reset-url="<?php echo e(route('admin.website.settings.reset', $key)); ?>"
                    data-reset-confirm="<?php echo e(__('Reset this setting to the config fallback?')); ?>"
                >
                    <?php echo e(__('Reset to fallback')); ?>

                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\website\settings\field.blade.php ENDPATH**/ ?>