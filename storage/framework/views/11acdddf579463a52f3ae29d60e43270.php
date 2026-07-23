<?php $__env->startSection('content'); ?>
    <div class="login-scene" aria-hidden="false">
        <div
            class="login-scene__background login-scene__background--active"
            style="background-image: url('<?php echo e(asset('images/login/background.jpg')); ?>')"
            aria-hidden="true"
        ></div>

        <div class="login-scene__overlay" aria-hidden="true"></div>

        <canvas class="login-scene__particles" data-login-particles aria-hidden="true"></canvas>

        <main class="login-scene__main" aria-label="<?php echo e(__('Sign in to Jana Prints')); ?>">
            <div class="login-card" data-login-card>
                <header class="login-card__header">
                    <a href="<?php echo e(url('/')); ?>" class="login-card__brand">
                        <span class="login-card__mark-wrap" aria-hidden="true">
                            <span class="login-card__mark-glow"></span>
                            <img
                                src="<?php echo e($brandingLogoUrl); ?>"
                                alt=""
                                class="login-card__mark"
                                width="44"
                                height="44"
                                decoding="async"
                            >
                        </span>
                        <span class="login-card__brand-text">
                            <span class="login-card__name"><?php echo e(config('site.name', 'Jana Prints')); ?></span>
                            <span class="login-card__tagline"><?php echo e(__('Print')); ?> &bull; <?php echo e(__('Brand')); ?> &bull; <?php echo e(__('Deliver')); ?></span>
                        </span>
                    </a>
                </header>

                <?php if(session('status')): ?>
                    <div class="login-alert" role="status"><?php echo e(session('status')); ?></div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="login-alert login-alert--error" role="alert"><?php echo e($errors->first()); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.login')); ?>" class="login-form" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="login-field">
                        <label for="email" class="login-field__label">Email</label>
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

                    <div class="login-field">
                        <label for="password" class="login-field__label">Password</label>
                        <div class="login-field__input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="login-field__input pr-12 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> login-field__input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                data-login-password-input
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-describedby="password-error" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            >
                            <button
                                type="button"
                                class="login-field__toggle"
                                data-login-password-toggle
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <span data-login-password-show aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1 1 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                <span data-login-password-hide hidden aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="login-field__error" id="password-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="login-form__meta">
                        <div class="login-remember">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="login-remember__checkbox"
                                <?php if(old('remember')): echo 'checked'; endif; ?>
                            >
                            <label for="remember_me" class="login-remember__label">Remember Me</label>
                        </div>
                        <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="login-form__forgot">Forgot Password</a>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="login-btn login-btn--primary">Sign In</button>
                </form>
            </div>
        </main>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth-login', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/auth/login.blade.php ENDPATH**/ ?>