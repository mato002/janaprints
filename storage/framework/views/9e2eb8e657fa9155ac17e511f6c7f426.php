<?php $__env->startSection('page-title', $portal === 'client' ? __('Set New Client Password') : __('Set New Password')); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal05c59d0954ce8352cf56d6e5564fe191 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal05c59d0954ce8352cf56d6e5564fe191 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.login-card','data' => ['title' => __('Choose a new password'),'subtitle' => __('Enter your email and a new password below to complete the reset.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.login-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Choose a new password')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enter your email and a new password below to complete the reset.'))]); ?>
        <form method="POST" action="<?php echo e($portal === 'client' ? route('client.password.store') : route('password.store')); ?>" class="login-form" novalidate>
            <?php echo csrf_field(); ?>

            <input type="hidden" name="token" value="<?php echo e($request->route('token')); ?>">

            <div class="login-field">
                <label for="email" class="login-field__label"><?php echo e(__('Email')); ?></label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo e(old('email', $request->email)); ?>"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@company.com"
                    class="login-field__input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> login-field__input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                >
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="login-field__error" id="email-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.login-password-field','data' => ['id' => 'password','name' => 'password','label' => __('New password'),'required' => true,'autocomplete' => 'new-password','placeholder' => __('Enter a new password')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.login-password-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password','name' => 'password','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New password')),'required' => true,'autocomplete' => 'new-password','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enter a new password'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a)): ?>
<?php $attributes = $__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a; ?>
<?php unset($__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a)): ?>
<?php $component = $__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a; ?>
<?php unset($__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.login-password-field','data' => ['id' => 'password_confirmation','name' => 'password_confirmation','label' => __('Confirm new password'),'required' => true,'autocomplete' => 'new-password','placeholder' => __('Confirm your new password')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.login-password-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'password_confirmation','name' => 'password_confirmation','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Confirm new password')),'required' => true,'autocomplete' => 'new-password','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Confirm your new password'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a)): ?>
<?php $attributes = $__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a; ?>
<?php unset($__attributesOriginal34c62057e6bd76a3e99f96f7eb775f1a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a)): ?>
<?php $component = $__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a; ?>
<?php unset($__componentOriginal34c62057e6bd76a3e99f96f7eb775f1a); ?>
<?php endif; ?>

            <button type="submit" class="login-btn login-btn--primary"><?php echo e(__('Reset password')); ?></button>

            <p class="login-form__footer">
                <a href="<?php echo e($portal === 'client' ? route('client.login') : route('admin.login')); ?>" class="login-form__forgot">
                    <?php echo e(__('Back to sign in')); ?>

                </a>
            </p>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal05c59d0954ce8352cf56d6e5564fe191)): ?>
<?php $attributes = $__attributesOriginal05c59d0954ce8352cf56d6e5564fe191; ?>
<?php unset($__attributesOriginal05c59d0954ce8352cf56d6e5564fe191); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal05c59d0954ce8352cf56d6e5564fe191)): ?>
<?php $component = $__componentOriginal05c59d0954ce8352cf56d6e5564fe191; ?>
<?php unset($__componentOriginal05c59d0954ce8352cf56d6e5564fe191); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth-login', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>