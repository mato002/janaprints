<ul class="<?php echo \Illuminate\Support\Arr::toCssClasses(['space-y-1', 'ml-4 border-l border-erp-border pl-3' => $depth > 0]); ?>">
    <?php $__currentLoopData = $nodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $account = $node['account']; ?>
        <li>
            <div class="flex flex-wrap items-center gap-2 rounded-md py-1 pr-2 text-sm hover:bg-erp-page/80">
                <a href="<?php echo e(route('admin.accounting.accounts.show', $account)); ?>" class="font-mono text-[11px] text-erp-accent hover:text-erp-accent-hover">
                    <?php echo e($account->code); ?>

                </a>
                <a href="<?php echo e(route('admin.accounting.accounts.show', $account)); ?>" class="font-medium text-erp-primary hover:text-erp-accent">
                    <?php echo e($account->name); ?>

                </a>
                <span class="text-[10px] text-slate-400"><?php echo e($account->normal_balance->label()); ?></span>
                <?php if(! $account->is_postable): ?>
                    <span class="erp-badge text-[10px]"><?php echo e(__('Header')); ?></span>
                <?php endif; ?>
                <?php if($account->is_system): ?>
                    <span class="erp-badge text-[10px]"><?php echo e(__('System')); ?></span>
                <?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => match($account->status) {
                    App\Enums\GlAccountStatus::Active => 'success',
                    App\Enums\GlAccountStatus::Inactive => 'neutral',
                    App\Enums\GlAccountStatus::Locked => 'warning',
                }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($account->status) {
                    App\Enums\GlAccountStatus::Active => 'success',
                    App\Enums\GlAccountStatus::Inactive => 'neutral',
                    App\Enums\GlAccountStatus::Locked => 'warning',
                })]); ?><?php echo e($account->status->label()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Accounting\GlAccount::class)): ?>
                    <a href="<?php echo e(route('admin.accounting.accounts.create', ['parent_id' => $account->id, 'type_id' => $account->gl_account_type_id])); ?>" class="text-[10px] text-erp-accent"><?php echo e(__('Add child')); ?></a>
                <?php endif; ?>
            </div>
            <?php if($node['children'] !== []): ?>
                <?php echo $__env->make('admin.accounting.accounts.partials.account-tree', ['nodes' => $node['children'], 'depth' => $depth + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\accounts\partials\account-tree.blade.php ENDPATH**/ ?>