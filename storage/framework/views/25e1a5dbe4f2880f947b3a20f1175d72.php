<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
]));

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

foreach (array_filter(([
    'title',
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="login-scene" aria-hidden="false">
    <div
        class="login-scene__background login-scene__background--active"
        style="background-image: url('<?php echo e(asset('images/login/background.jpg')); ?>')"
        aria-hidden="true"
    ></div>

    <div class="login-scene__overlay" aria-hidden="true"></div>

    <canvas class="login-scene__particles" data-login-particles aria-hidden="true"></canvas>

    <main class="login-scene__main" aria-label="<?php echo e($title); ?>">
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

            <div class="login-card__intro">
                <h1 class="login-card__title"><?php echo e($title); ?></h1>
                <?php if($subtitle): ?>
                    <p class="login-card__subtitle"><?php echo e($subtitle); ?></p>
                <?php endif; ?>
            </div>

            <?php if(session('status')): ?>
                <div class="login-alert" role="status"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="login-alert login-alert--error" role="alert"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <?php echo e($slot); ?>

        </div>
    </main>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\auth\login-card.blade.php ENDPATH**/ ?>