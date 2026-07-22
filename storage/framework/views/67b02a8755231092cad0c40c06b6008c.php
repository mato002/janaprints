<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['transparent' => true, 'portal' => false]));

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

foreach (array_filter((['transparent' => true, 'portal' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header
    data-public-header
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'public-header',
        'public-header--top' => $transparent,
        'public-header--portal' => $portal,
    ]); ?>"
>
    <div class="public-container public-container--wide">
        <div class="public-header__bar">
            <div class="public-header__brand-col">
                <a href="<?php echo e(route('home')); ?>" class="group flex items-center" aria-label="Jana Prints home">
                    <?php if (isset($component)) { $__componentOriginala8631124f8a79f981399d6e3c172e3b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8631124f8a79f981399d6e3c172e3b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.brand-logo','data' => ['full' => true,'header' => true,'class' => 'transition-opacity group-hover:opacity-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full' => true,'header' => true,'class' => 'transition-opacity group-hover:opacity-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $attributes = $__attributesOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $component = $__componentOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__componentOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
                </a>
            </div>

            <nav class="public-header__nav items-center gap-6 lg:gap-8" aria-label="Primary">
                <a href="<?php echo e(route('storefront.services')); ?>" class="public-header__link public-nav-link">Services</a>
                <a href="<?php echo e(route('storefront.products')); ?>" class="public-header__link public-nav-link">Products</a>
                <a href="<?php echo e(route('storefront.gallery')); ?>" class="public-header__link public-nav-link">Gallery</a>
                <a href="<?php echo e($aboutSectionHref); ?>" class="public-header__link public-nav-link">About</a>
                <a href="<?php echo e($contactSectionHref); ?>" class="public-header__link public-nav-link">Contact</a>
            </nav>

            <div class="public-header__actions">
                <?php if($portal): ?>
                    <div class="hidden md:block">
                        <?php if (isset($component)) { $__componentOriginal25007cadfc73ded27790030db47eb5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25007cadfc73ded27790030db47eb5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.profile-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.profile-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25007cadfc73ded27790030db47eb5cf)): ?>
<?php $attributes = $__attributesOriginal25007cadfc73ded27790030db47eb5cf; ?>
<?php unset($__attributesOriginal25007cadfc73ded27790030db47eb5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25007cadfc73ded27790030db47eb5cf)): ?>
<?php $component = $__componentOriginal25007cadfc73ded27790030db47eb5cf; ?>
<?php unset($__componentOriginal25007cadfc73ded27790030db47eb5cf); ?>
<?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isClientPortalAccount()): ?>
                            <a href="<?php echo e(route('client.dashboard')); ?>" class="public-header__link hidden text-sm font-medium md:inline">
                                <?php echo e(__('Client Portal')); ?>

                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if(Route::has('client.login')): ?>
                            <a href="<?php echo e(route('client.login')); ?>" class="public-header__link hidden text-sm font-medium md:inline">
                                <?php echo e(__('Client Login')); ?>

                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="<?php echo e($quoteFormHref); ?>" class="public-header__quote-btn public-btn--primary public-btn--sm hidden shadow-brand-glow md:inline-flex">
                    <?php echo e(__('Request Quote')); ?>

                </a>

                <button
                    type="button"
                    class="public-header__menu-btn md:hidden"
                    data-mobile-nav-toggle
                    aria-expanded="false"
                    aria-controls="public-mobile-nav"
                    aria-label="Open menu"
                >
                    <svg class="public-header__menu-icon public-header__menu-icon--open h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="public-header__menu-icon public-header__menu-icon--close h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    
    <div class="public-mobile-nav" id="public-mobile-nav" data-mobile-nav hidden>
        <nav class="public-mobile-nav__panel" aria-label="Mobile navigation">
            <ul class="public-mobile-nav__links">
                <li><a href="<?php echo e(route('home')); ?>">Home</a></li>
                <li><a href="<?php echo e(route('storefront.services')); ?>">Services</a></li>
                <li><a href="<?php echo e(route('storefront.products')); ?>">Products</a></li>
                <li><a href="<?php echo e(route('storefront.gallery')); ?>">Gallery</a></li>
                <li><a href="<?php echo e($aboutSectionHref); ?>">About</a></li>
                <li><a href="<?php echo e($contactSectionHref); ?>">Contact</a></li>
                <?php if($portal): ?>
                    <li class="public-mobile-nav__divider" aria-hidden="true"></li>
                    <li class="public-mobile-nav__section-label"><?php echo e(__('My account')); ?></li>
                    <li><a href="<?php echo e(route('client.dashboard')); ?>"><?php echo e(__('Overview')); ?></a></li>
                    <li><a href="<?php echo e(route('client.quotations.index')); ?>"><?php echo e(__('Quotes')); ?></a></li>
                    <li><a href="<?php echo e(route('client.orders.index')); ?>"><?php echo e(__('Orders')); ?></a></li>
                    <li><a href="<?php echo e(route('client.invoices.index')); ?>"><?php echo e(__('Invoices')); ?></a></li>
                    <li><a href="<?php echo e(route('client.payments.index')); ?>"><?php echo e(__('Payments')); ?></a></li>
                    <li><a href="<?php echo e(route('client.artwork.index')); ?>"><?php echo e(__('Artwork')); ?></a></li>
                    <li><a href="<?php echo e(route('client.account.edit')); ?>"><?php echo e(__('Account settings')); ?></a></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="public-mobile-nav__signout"><?php echo e(__('Sign out')); ?></button>
                        </form>
                    </li>
                <?php else: ?>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isClientPortalAccount()): ?>
                            <li><a href="<?php echo e(route('client.dashboard')); ?>"><?php echo e(__('Client Portal')); ?></a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if(Route::has('client.login')): ?>
                            <li><a href="<?php echo e(route('client.login')); ?>"><?php echo e(__('Client Login')); ?></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="public-mobile-nav__cta-wrap">
                <a href="<?php echo e($quoteFormHref); ?>" class="public-mobile-nav__cta">
                    Request Quote
                </a>
            </div>
        </nav>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\header.blade.php ENDPATH**/ ?>