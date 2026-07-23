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

<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('General')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('General'))]); ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label"><?php echo e(__('Provider')); ?></label>
                <select name="provider" class="erp-select w-full" required>
                    <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($provider->value); ?>" <?php if(old('provider', $setting?->provider?->value) === $provider->value): echo 'selected'; endif; ?>><?php echo e($provider->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('From Name')); ?></label>
                <input type="text" name="from_name" value="<?php echo e(old('from_name', $setting?->from_name)); ?>" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('From Email')); ?></label>
                <input type="email" name="from_email" value="<?php echo e(old('from_email', $setting?->from_email)); ?>" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Reply-To Email')); ?></label>
                <input type="email" name="reply_to_email" value="<?php echo e(old('reply_to_email', $setting?->reply_to_email)); ?>" class="erp-input w-full">
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('SMTP')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SMTP'))]); ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label"><?php echo e(__('Host')); ?></label><input type="text" name="smtp_host" value="<?php echo e(old('smtp_host', $setting?->smtp_host)); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Port')); ?></label><input type="number" name="smtp_port" value="<?php echo e(old('smtp_port', $setting?->smtp_port)); ?>" class="erp-input w-full"></div>
            <div>
                <label class="erp-label"><?php echo e(__('Encryption')); ?></label>
                <select name="smtp_encryption" class="erp-select w-full">
                    <option value=""><?php echo e(__('None')); ?></option>
                    <option value="tls" <?php if(old('smtp_encryption', $setting?->smtp_encryption) === 'tls'): echo 'selected'; endif; ?>>TLS</option>
                    <option value="ssl" <?php if(old('smtp_encryption', $setting?->smtp_encryption) === 'ssl'): echo 'selected'; endif; ?>>SSL</option>
                </select>
            </div>
            <div><label class="erp-label"><?php echo e(__('Username')); ?></label><input type="text" name="smtp_username" value="<?php echo e(old('smtp_username', $setting?->smtp_username)); ?>" class="erp-input w-full" autocomplete="off"></div>
            <div class="sm:col-span-2">
                <label class="erp-label"><?php echo e(__('Password')); ?></label>
                <input type="password" name="smtp_password" class="erp-input w-full" placeholder="<?php echo e($setting?->hasCredential('smtp_password') ? __('Leave blank to keep current') : ''); ?>" autocomplete="new-password">
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Mailgun')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Mailgun'))]); ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label"><?php echo e(__('Domain')); ?></label><input type="text" name="mailgun_domain" value="<?php echo e(old('mailgun_domain', $setting?->mailgun_domain)); ?>" class="erp-input w-full"></div>
            <div>
                <label class="erp-label"><?php echo e(__('API Key')); ?></label>
                <input type="password" name="mailgun_api_key" class="erp-input w-full" placeholder="<?php echo e($setting?->hasCredential('mailgun_api_key') ? __('Leave blank to keep current') : ''); ?>" autocomplete="off">
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('SendGrid')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SendGrid'))]); ?>
        <div>
            <label class="erp-label"><?php echo e(__('API Key')); ?></label>
            <input type="password" name="sendgrid_api_key" class="erp-input w-full" placeholder="<?php echo e($setting?->hasCredential('sendgrid_api_key') ? __('Leave blank to keep current') : ''); ?>" autocomplete="off">
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-section','data' => ['title' => __('Amazon SES')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Amazon SES'))]); ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label"><?php echo e(__('Access Key')); ?></label><input type="text" name="ses_access_key" value="<?php echo e(old('ses_access_key', $setting?->ses_access_key)); ?>" class="erp-input w-full" autocomplete="off"></div>
            <div>
                <label class="erp-label"><?php echo e(__('Secret Key')); ?></label>
                <input type="password" name="ses_secret_key" class="erp-input w-full" placeholder="<?php echo e($setting?->hasCredential('ses_secret_key') ? __('Leave blank to keep current') : ''); ?>" autocomplete="off">
            </div>
            <div><label class="erp-label"><?php echo e(__('Region')); ?></label><input type="text" name="ses_region" value="<?php echo e(old('ses_region', $setting?->ses_region)); ?>" class="erp-input w-full" placeholder="us-east-1"></div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $attributes = $__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__attributesOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3)): ?>
<?php $component = $__componentOriginalba421f08b6b43aecb09f8eebe577a4f3; ?>
<?php unset($__componentOriginalba421f08b6b43aecb09f8eebe577a4f3); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\integrations\email\form.blade.php ENDPATH**/ ?>