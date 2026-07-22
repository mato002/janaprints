<?php $__env->startSection('page-title', $portal === 'client' ? __('Reset Client Password') : __('Reset Password')); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal05c59d0954ce8352cf56d6e5564fe191 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal05c59d0954ce8352cf56d6e5564fe191 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.login-card','data' => ['title' => __('Forgot your password?'),'subtitle' => __('Enter your email and we will send you a secure link to choose a new password.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.login-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Forgot your password?')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enter your email and we will send you a secure link to choose a new password.'))]); ?>
        <form method="POST" action="<?php echo e($portal === 'client' ? route('client.password.email') : route('password.email')); ?>" class="login-form" novalidate>
            <?php echo csrf_field(); ?>

            <div class="login-field">
                <label for="email" class="login-field__label"><?php echo e(__('Email')); ?></label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
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

            <button type="submit" class="login-btn login-btn--primary"><?php echo e(__('Email reset link')); ?></button>

            <?php if($portal !== 'client'): ?>
                <p class="login-form__footer">
                    <?php echo e(__('Customer portal user?')); ?>

                    <a href="<?php echo e(route('client.password.request')); ?>" class="login-form__forgot"><?php echo e(__('Reset client portal password')); ?></a>
                </p>
            <?php endif; ?>

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

<?php echo $__env->make('layouts.auth-login', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>