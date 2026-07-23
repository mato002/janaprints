<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Code')); ?></span>
            <input name="code" value="<?php echo e(old('code', $template?->code)); ?>" class="erp-input w-full font-mono" <?php if($template): echo 'disabled'; endif; ?> required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Name')); ?></span>
            <input name="name" value="<?php echo e(old('name', $template?->name)); ?>" class="erp-input w-full" required>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Module')); ?></span>
            <select name="module" class="erp-input w-full" required>
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($module->value); ?>" <?php if(old('module', $template?->module?->value) === $module->value): echo 'selected'; endif; ?>><?php echo e($module->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Active')); ?></span>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="erp-checkbox" <?php if(old('is_active', $template?->is_active ?? true)): echo 'checked'; endif; ?>>
        </label>
        <label class="block text-sm sm:col-span-2">
            <span class="mb-1 block text-slate-600"><?php echo e(__('Description')); ?></span>
            <textarea name="description" rows="2" class="erp-input w-full"><?php echo e(old('description', $template?->description)); ?></textarea>
        </label>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h2 class="erp-card-title mb-3"><?php echo e(__('Template lines')); ?></h2>
    <div class="space-y-3" x-data="{ lines: <?php echo \Illuminate\Support\Js::from($oldLines)->toHtml() ?>, add() { this.lines.push({ entry_side: 'debit', account_resolver: 'account_key', amount_source: 'total_amount', line_description: ':description' }) } }">
        <template x-for="(line, index) in lines" :key="index">
            <div class="grid gap-2 rounded border border-erp-border p-3 sm:grid-cols-3 lg:grid-cols-6">
                <label class="text-xs">
                    <?php echo e(__('Side')); ?>

                    <select class="erp-input w-full" :name="`lines[${index}][entry_side]`" x-model="line.entry_side">
                        <?php $__currentLoopData = $sides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $side): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($side->value); ?>"><?php echo e(ucfirst($side->value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="text-xs">
                    <?php echo e(__('Resolver')); ?>

                    <select class="erp-input w-full" :name="`lines[${index}][account_resolver]`" x-model="line.account_resolver">
                        <?php $__currentLoopData = $resolvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resolver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($resolver->value); ?>"><?php echo e($resolver->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'fixed_account'">
                    <?php echo e(__('GL account')); ?>

                    <select class="erp-input w-full" :name="`lines[${index}][gl_account_id]`" x-model="line.gl_account_id">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($account->id); ?>"><?php echo e($account->code); ?> — <?php echo e($account->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'account_key'">
                    <?php echo e(__('Account key')); ?>

                    <select class="erp-input w-full" :name="`lines[${index}][account_key]`" x-model="line.account_key">
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $accountKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($key); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="text-xs" x-show="line.account_resolver === 'context_account'">
                    <?php echo e(__('Context field')); ?>

                    <input class="erp-input w-full" :name="`lines[${index}][context_account_field]`" x-model="line.context_account_field">
                </label>
                <label class="text-xs">
                    <?php echo e(__('Amount')); ?>

                    <select class="erp-input w-full" :name="`lines[${index}][amount_source]`" x-model="line.amount_source">
                        <?php $__currentLoopData = $amountSources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($source->value); ?>"><?php echo e($source->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="text-xs" x-show="line.amount_source === 'context_field'">
                    <?php echo e(__('Amount field')); ?>

                    <input class="erp-input w-full" :name="`lines[${index}][amount_field]`" x-model="line.amount_field">
                </label>
                <label class="text-xs sm:col-span-2">
                    <?php echo e(__('Description')); ?>

                    <input class="erp-input w-full" :name="`lines[${index}][line_description]`" x-model="line.line_description">
                </label>
                <div class="flex items-end">
                    <button type="button" class="erp-btn-secondary text-xs" @click="lines.splice(index, 1)" x-show="lines.length > 1"><?php echo e(__('Remove')); ?></button>
                </div>
            </div>
        </template>
        <button type="button" class="erp-btn-secondary" @click="add()"><?php echo e(__('Add line')); ?></button>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\posting\templates\partials\form.blade.php ENDPATH**/ ?>