<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Account'),'heading' => __('Account settings')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Account')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Account settings'))]); ?>
    <p class="client-lead">
        <?php echo e(__('Update your contact details and password. Changes sync to your customer record so your account team sees the latest information.')); ?>

    </p>

    <form method="POST" action="<?php echo e(route('client.account.update')); ?>" class="client-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <h2 class="client-form__section"><?php echo e(__('Contact details')); ?></h2>

        <div class="client-form__group">
            <label for="name" class="client-label"><?php echo e(__('Contact name')); ?></label>
            <input id="name" name="name" type="text" class="client-input" value="<?php echo e(old('name', $user->name)); ?>" required>
        </div>

        <div class="client-form__group">
            <label for="phone" class="client-label"><?php echo e(__('Phone')); ?></label>
            <input id="phone" name="phone" type="tel" class="client-input" value="<?php echo e(old('phone', $customer->phone)); ?>" autocomplete="tel">
        </div>

        <div class="client-form__group">
            <label for="alternative_phone" class="client-label"><?php echo e(__('Alternative phone')); ?></label>
            <input id="alternative_phone" name="alternative_phone" type="tel" class="client-input" value="<?php echo e(old('alternative_phone', $customer->alternative_phone)); ?>" autocomplete="tel">
        </div>

        <div class="client-form__group">
            <label for="city" class="client-label"><?php echo e(__('City')); ?></label>
            <input id="city" name="city" type="text" class="client-input" value="<?php echo e(old('city', $customer->city)); ?>">
        </div>

        <div class="client-form__group">
            <label for="physical_address" class="client-label"><?php echo e(__('Physical address')); ?></label>
            <textarea id="physical_address" name="physical_address" rows="3" class="client-input"><?php echo e(old('physical_address', $customer->physical_address)); ?></textarea>
        </div>

        <div class="client-form__group">
            <label for="postal_address" class="client-label"><?php echo e(__('Postal address')); ?></label>
            <input id="postal_address" name="postal_address" type="text" class="client-input" value="<?php echo e(old('postal_address', $customer->postal_address)); ?>">
        </div>

        <div class="client-form__group">
            <label for="website" class="client-label"><?php echo e(__('Website')); ?></label>
            <input id="website" name="website" type="url" class="client-input" value="<?php echo e(old('website', $customer->website)); ?>" placeholder="https://">
        </div>

        <div class="client-form__group">
            <label class="client-label"><?php echo e(__('Company')); ?></label>
            <input type="text" class="client-input" value="<?php echo e($customer->company_name); ?>" disabled>
            <p class="client-form__hint"><?php echo e(__('Contact your account manager to change your company name.')); ?></p>
        </div>

        <div class="client-form__group">
            <label class="client-label"><?php echo e(__('Login email')); ?></label>
            <input type="email" class="client-input" value="<?php echo e($user->email); ?>" disabled>
            <p class="client-form__hint"><?php echo e(__('Email changes are handled by your account team.')); ?></p>
        </div>

        <hr class="client-divider">

        <h2 class="client-form__section"><?php echo e(__('Password')); ?></h2>
        <p class="client-form__hint"><?php echo e(__('Leave blank to keep your current password.')); ?></p>

        <?php if (isset($component)) { $__componentOriginal8c5da219bafe1ad7bde745cc316fb44d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.password-field','data' => ['id' => 'password','name' => 'password','label' => __('New password'),'autocomplete' => 'new-password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.password-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password','name' => 'password','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New password')),'autocomplete' => 'new-password']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d)): ?>
<?php $attributes = $__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d; ?>
<?php unset($__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c5da219bafe1ad7bde745cc316fb44d)): ?>
<?php $component = $__componentOriginal8c5da219bafe1ad7bde745cc316fb44d; ?>
<?php unset($__componentOriginal8c5da219bafe1ad7bde745cc316fb44d); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal8c5da219bafe1ad7bde745cc316fb44d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.password-field','data' => ['id' => 'password_confirmation','name' => 'password_confirmation','label' => __('Confirm new password'),'autocomplete' => 'new-password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.password-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password_confirmation','name' => 'password_confirmation','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Confirm new password')),'autocomplete' => 'new-password']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d)): ?>
<?php $attributes = $__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d; ?>
<?php unset($__attributesOriginal8c5da219bafe1ad7bde745cc316fb44d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c5da219bafe1ad7bde745cc316fb44d)): ?>
<?php $component = $__componentOriginal8c5da219bafe1ad7bde745cc316fb44d; ?>
<?php unset($__componentOriginal8c5da219bafe1ad7bde745cc316fb44d); ?>
<?php endif; ?>

        <button type="submit" class="client-btn"><?php echo e(__('Save changes')); ?></button>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\account\edit.blade.php ENDPATH**/ ?>