<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['webhook' => null, 'events', 'statuses']));

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

foreach (array_filter((['webhook' => null, 'events', 'statuses']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-4">
    <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input type="text" name="name" value="<?php echo e(old('name', $webhook?->name)); ?>" class="erp-input w-full" required></div>
    <div><label class="erp-label"><?php echo e(__('Endpoint URL')); ?></label><input type="url" name="endpoint_url" value="<?php echo e(old('endpoint_url', $webhook?->endpoint_url)); ?>" class="erp-input w-full" required></div>
    <div>
        <label class="erp-label"><?php echo e(__('Secret')); ?></label>
        <input type="password" name="secret" class="erp-input w-full" placeholder="<?php echo e($webhook?->secret ? __('Leave blank to keep current') : __('Auto-generated if blank')); ?>" autocomplete="off">
    </div>
    <div>
        <label class="erp-label"><?php echo e(__('Status')); ?></label>
        <select name="status" class="erp-select w-full">
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status->value); ?>" <?php if(old('status', $webhook?->status?->value ?? 'active') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label class="erp-label"><?php echo e(__('Retry count')); ?></label><input type="number" name="retry_count" value="<?php echo e(old('retry_count', $webhook?->retry_count ?? 3)); ?>" class="erp-input w-full" min="0" max="10"></div>
    <div>
        <label class="erp-label"><?php echo e(__('Event types')); ?></label>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="event_types[]" value="<?php echo e($event->value); ?>" <?php if(in_array($event->value, old('event_types', $webhook?->event_types ?? []), true)): echo 'checked'; endif; ?>>
                    <?php echo e($event->label()); ?>

                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\integrations\webhooks\form.blade.php ENDPATH**/ ?>