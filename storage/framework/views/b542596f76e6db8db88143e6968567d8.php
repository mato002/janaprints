<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['setting' => null, 'providers']));

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

foreach (array_filter((['setting' => null, 'providers']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="erp-label"><?php echo e(__('Provider')); ?></label>
        <select name="provider" class="erp-select w-full" required>
            <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($provider->value); ?>" <?php if(old('provider', $setting?->provider?->value) === $provider->value): echo 'selected'; endif; ?>><?php echo e($provider->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label class="erp-label"><?php echo e(__('Sender ID')); ?></label><input type="text" name="sender_id" value="<?php echo e(old('sender_id', $setting?->sender_id)); ?>" class="erp-input w-full"></div>
    <div class="sm:col-span-2"><label class="erp-label"><?php echo e(__('API URL')); ?></label><input type="url" name="api_url" value="<?php echo e(old('api_url', $setting?->api_url)); ?>" class="erp-input w-full"></div>
    <div>
        <label class="erp-label"><?php echo e(__('API Key')); ?></label>
        <input type="password" name="api_key" class="erp-input w-full" placeholder="<?php echo e($setting?->api_key ? __('Leave blank to keep current') : ''); ?>" autocomplete="off">
    </div>
    <div><label class="erp-label"><?php echo e(__('Username')); ?></label><input type="text" name="username" value="<?php echo e(old('username', $setting?->username)); ?>" class="erp-input w-full" autocomplete="off"></div>
    <div>
        <label class="erp-label"><?php echo e(__('Password')); ?></label>
        <input type="password" name="password" class="erp-input w-full" placeholder="<?php echo e($setting?->password ? __('Leave blank to keep current') : ''); ?>" autocomplete="off">
    </div>
    <div class="sm:col-span-2"><label class="erp-label"><?php echo e(__('Callback URL')); ?></label><input type="url" name="callback_url" value="<?php echo e(old('callback_url', $setting?->callback_url)); ?>" class="erp-input w-full"></div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\integrations\sms\form.blade.php ENDPATH**/ ?>