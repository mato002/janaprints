<?php
    $field = str_replace('.', '_', $key);
    $resolved = $resolver->get($key, null, $companyId);
    $displayValue = old($field, $resolved);
?>

<div
    class="<?php echo \Illuminate\Support\Arr::toCssClasses(['lg:col-span-2' => in_array($meta['type'], ['text'], true)]); ?>"
    data-settings-field
    data-settings-label="<?php echo e(strtolower($meta['label'])); ?>"
    data-settings-key="<?php echo e(strtolower($key)); ?>"
>
    <label class="erp-label" for="<?php echo e($field); ?>"><?php echo e($meta['label']); ?></label>
    <?php if(! empty($meta['description'])): ?>
        <p class="mb-1 text-xs text-slate-500"><?php echo e($meta['description']); ?></p>
    <?php endif; ?>

    <?php if($record?->fallback_value && $resolved !== null && (string) $record?->value !== (string) $record?->fallback_value): ?>
        <p class="mb-2 text-xs text-slate-500">
            <?php echo e(__('Fallback:')); ?>

            <span class="font-mono"><?php echo e(Str::limit((string) ($record?->fallback_value ?? $resolved), 80)); ?></span>
        </p>
    <?php endif; ?>

    <?php if($meta['type'] === 'text'): ?>
        <textarea id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" rows="4" class="erp-input"><?php echo e($displayValue); ?></textarea>
    <?php else: ?>
        <input
            id="<?php echo e($field); ?>"
            name="<?php echo e($field); ?>"
            type="<?php echo e(in_array($meta['type'], ['email'], true) ? $meta['type'] : 'text'); ?>"
            class="erp-input"
            value="<?php echo e($displayValue); ?>"
        >
    <?php endif; ?>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <p class="font-mono text-xs text-slate-400"><?php echo e($key); ?></p>
        <?php if($record?->fallback_value): ?>
            <span class="text-xs text-slate-500"><?php echo e(__('Config fallback preserved in database.')); ?></span>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', App\Models\DocumentSetting::class)): ?>
            <?php if($record?->value): ?>
                <button
                    type="button"
                    class="text-xs text-slate-500 underline hover:text-slate-700"
                    data-document-settings-reset-trigger
                    data-reset-url="<?php echo e(route('admin.documents.settings.reset', $key)); ?>"
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\documents\settings\field.blade.php ENDPATH**/ ?>