<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'compact' => false,
    'variant' => 'default',
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
    'compact' => false,
    'variant' => 'default',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $isTopbar = $variant === 'topbar';
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['client-profile-menu', 'client-profile-menu--topbar' => $isTopbar]); ?>" data-client-profile-menu>
    <button
        type="button"
        class="client-profile-menu__trigger"
        data-client-profile-toggle
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="client-profile-dropdown"
        aria-label="<?php echo e(__('Open account menu')); ?>"
    >
        <span class="client-profile-menu__avatar" aria-hidden="true"><?php echo e($initials ?: 'C'); ?></span>
        <?php if (! ($compact || $isTopbar)): ?>
            <span class="client-profile-menu__trigger-text">
                <span class="client-profile-menu__name"><?php echo e($user->name); ?></span>
                <span class="client-profile-menu__company"><?php echo e($user->customer?->company_name); ?></span>
            </span>
            <svg class="client-profile-menu__chevron h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        <?php endif; ?>
        <?php if($isTopbar): ?>
            <svg class="client-profile-menu__chevron client-profile-menu__chevron--topbar h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        <?php endif; ?>
    </button>

    <?php if($isTopbar): ?>
        <div class="client-profile-menu__backdrop" data-client-profile-backdrop hidden aria-hidden="true"></div>
    <?php endif; ?>

    <div
        id="client-profile-dropdown"
        class="client-profile-menu__dropdown"
        data-client-profile-dropdown
        role="menu"
        hidden
    >
        <div class="client-profile-menu__header">
            <p class="client-profile-menu__header-name"><?php echo e($user->name); ?></p>
            <p class="client-profile-menu__header-company"><?php echo e($user->customer?->company_name); ?></p>
        </div>
        <div class="client-profile-menu__items">
            <a href="<?php echo e(route('client.account.edit')); ?>" class="client-profile-menu__item" role="menuitem" data-turbo-frame="client-main" data-turbo-action="advance">
                <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'user','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
                <?php echo e(__('Account settings')); ?>

            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" data-turbo-frame="_top">
                <?php echo csrf_field(); ?>
                <button type="submit" class="client-profile-menu__item client-profile-menu__item--danger" role="menuitem">
                    <?php if (isset($component)) { $__componentOriginala2f97c54b2eb74e6513efba3de7afc52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.client.icon','data' => ['name' => 'logout','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('client.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $attributes = $__attributesOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__attributesOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52)): ?>
<?php $component = $__componentOriginala2f97c54b2eb74e6513efba3de7afc52; ?>
<?php unset($__componentOriginala2f97c54b2eb74e6513efba3de7afc52); ?>
<?php endif; ?>
                    <?php echo e(__('Sign out')); ?>

                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\client\profile-menu.blade.php ENDPATH**/ ?>