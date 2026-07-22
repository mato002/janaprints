<?php
    $field = str_replace('.', '_', $key);
    $rows = old($field);

    if ($rows === null) {
        $rows = is_array($resolved) ? $resolved : [];
    } elseif (is_string($rows)) {
        $rows = json_decode($rows, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    $isNav = $key === 'footer.nav';
    $isBadges = $key === 'footer.trust_badges';
?>

<div class="lg:col-span-2" data-json-rows-editor data-json-field="<?php echo e($field); ?>" data-settings-field data-settings-label="<?php echo e(strtolower($meta['label'])); ?>" data-settings-key="<?php echo e(strtolower($key)); ?>">
    <label class="erp-label"><?php echo e($meta['label']); ?></label>
    <?php if(! empty($meta['description'])): ?>
        <p class="mb-2 text-xs text-slate-500"><?php echo e($meta['description']); ?></p>
    <?php endif; ?>

    <div class="space-y-2" data-json-rows-list data-empty-label="<?php echo e(__('No rows yet. Add one below.')); ?>">
        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3" data-json-row>
                <?php if($isNav): ?>
                    <input
                        type="text"
                        name="<?php echo e($field); ?>[<?php echo e($index); ?>][label]"
                        value="<?php echo e(is_array($row) ? ($row['label'] ?? '') : ''); ?>"
                        class="erp-input min-w-[8rem] flex-1"
                        placeholder="<?php echo e(__('Link label')); ?>"
                        required
                    >
                    <input
                        type="text"
                        name="<?php echo e($field); ?>[<?php echo e($index); ?>][href]"
                        value="<?php echo e(is_array($row) ? ($row['href'] ?? '') : ''); ?>"
                        class="erp-input min-w-[12rem] flex-[2]"
                        placeholder="<?php echo e(__('URL or path, e.g. /services')); ?>"
                        required
                    >
                <?php elseif($isBadges): ?>
                    <input
                        type="text"
                        name="<?php echo e($field); ?>[<?php echo e($index); ?>]"
                        value="<?php echo e(is_string($row) ? $row : ''); ?>"
                        class="erp-input flex-1"
                        placeholder="<?php echo e(__('Badge label')); ?>"
                        required
                    >
                <?php endif; ?>
                <button type="button" class="erp-btn-secondary text-xs" data-json-row-remove><?php echo e(__('Remove')); ?></button>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-xs text-slate-500" data-json-rows-empty><?php echo e(__('No rows yet. Add one below.')); ?></p>
        <?php endif; ?>
    </div>

    <button type="button" class="erp-btn-secondary mt-3 text-xs" data-json-row-add>
        <?php echo e($isNav ? __('Add navigation link') : __('Add badge')); ?>

    </button>

    <?php if($isNav): ?>
        <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Example:')); ?> <code>{"label":"Services","href":"/services"}</code></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\website\settings\json-rows.blade.php ENDPATH**/ ?>